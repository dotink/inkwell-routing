<?php namespace Inkwell\Routing
{
	use Dotink\Flourish;
	use Inkwell\Transport;

	/**
	 * Collection class responsible for aggregating and mapping routes to actions
	 *
	 * @copyright Copyright (c) 2015, Matthew J. Sahagian
	 * @author Matthew J. Sahagian [mjs] <msahagian@dotink.org>
	 *
	 * @license Please reference the LICENSE.md file at the root of this distribution
	 *
	 * @package Dotink\Inkwell
	 */
	class Collection
	{
		const DELIMITER = '#';

		/**
		 *
		 */
		private $handlers = array();


		/**
		 *
		 */
		protected $link = NULL;


		/**
		 *
		 */
		protected $links = array();


		/**
		 *
		 */
		protected $redirects = array();


		/**
		 *
		 */
		private $parser = NULL;


		/**
		 *
		 */
		public function __construct(ParserInterface $parser = NULL, CompilerInterface $compiler = NULL)
		{
			$this->parser   = $parser   ?: new Parser();
			$this->compiler = $compiler ?: new Compiler();
		}



		/**
		 *
		 */
		public function base($base, Callable $callback)
		{
			$group = new BaseGroup($this, $base);

			if ($callback) {
				$callback($group);
			}

			return $this;
		}

		/**
		 *
		 */
		public function getCompiler()
		{
			return $this->compiler;
		}


		/**
		 *
		 */
		public function getParser()
		{
			return $this->parser;
		}


		/**
		 * Handles an error with an action in the routes collection
		 *
		 * @access public
		 * @param string $base The base for all the routes
		 * @param string $status The status string
		 * @param mixed $action The action to call on error
		 * @return void
		 */
		public function handle($base, $status, $action)
		{
			$base = rtrim($base, '/');
			$hash = md5($base . $status);

			if (isset($this->handlers[$hash])) {
				throw new Flourish\ProgrammerException(
					'The base %s already has a handler registered for status %s.',
					$base,
					$status
				);
			}

			$this->handlers[$hash] = [
				'base'   => $base ?: '',
				'action' => $action,
				'status' => $status
			];
		}


		/**
		 *
		 */
		public function link($base, $route, $action)
		{
			$base     = rtrim($base, '/');
			$route    = ltrim($route, '/');
			$pattern  = $this->parser->regularize(
				$base . '/' . $route,
				static::DELIMITER,
				$params
			);

			if (isset($this->links[$pattern])) {
				try {
					$this->validateConflictedAction($action, $this->links[$pattern]['action']);

				} catch (Flourish\ProgrammerException $e) {
					throw new Flourish\ProgrammerException(
						'%s  Cannot add conflicting route %s.',
						$e->getMessage(),
						$route
					);
				}
			}

			$this->links[$pattern] = [
				'base'   => $base,
				'action' => $action,
				'params' => $params
			];
		}


		/**
		 * Redirects a route to a translation in the routes collection
		 *
		 * @access public
		 * @param string $route The route key/mapping
		 * @param string $translation The translation to map to
		 * @param integer $type The type of redirect (301, 303, 307, etc...)
		 * @return void
		 * @throws Flourish\ProgrammerException in the case of conflicting routes
		 */
		public function redirect($base, $route, $target, $type = 301)
		{
			$base    = rtrim($base, '/');
			$route   = ltrim($route, '/');
			$pattern = $this->parser->regularize(
				$base . '/' . $route,
				static::DELIMITER,
				$params
			);

			if (isset($this->redirects[$pattern])) {
				try {
					$this->validateConflictedTarget($target, $this->redirects[$pattern]['target']);
					$this->validateConflictedType($type, $this->redirects[$pattern]['type']);

				} catch (Flourish\ValidationException $e) {
					throw new Flourish\ProgrammerException(
						'%s  Cannot add conflicting redirect %s.',
						$e->getMessage(),
						$route
					);
				}
			}

			$this->redirects[$pattern] = [
				'base'   => $base,
				'params' => $params,
				'target' => $target,
				'type'   => $type
			];
		}


		/**
		 *
		 */
		public function reset()
		{
			$this->link     = reset($this->links);
			$this->redirect = reset($this->redirects);
		}


		/**
		 * Resolves a URL redirect
		 */
		public function resolve(Transport\Resource\Request $request, Transport\Resource\Response $response, $loose = FALSE)
		{
			if (!$this->redirect) {
				return FALSE;
			}

			$path   = $request->getTarget();
			$result = $this->match(key($this->redirects), $path, $loose);

			if (!$result) {
				$response->set(NULL);
				$response->setStatusCode(404);

			} else {
				$code   = $this->redirect['type'];
				$target = $this->redirect['target'];
				$params = array_map('urldecode', array_combine(
					$this->redirect['params'],
					$result['params']
				));

				if (is_string($target)) {
					$target = $this->compiler->make($target, $params, $remainder);
					$params = $remainder;
				}

				$response->set($target);
				$response->setStatusCode($code);
				$request->params->set($params);
			}

			$this->redirect = next($this->redirects);

			return TRUE;
		}


		/**
		 * Seeks the appropriate action for a given request
		 */
		public function seek(Transport\Resource\Request $request, Transport\Resource\Response $response, $loose = FALSE)
		{
			if (!$this->link) {
				return FALSE;
			}

			$path   = $request->getTarget();
			$result = $this->match(key($this->links), $path, $loose);

			if (!$result) {
				$response->set(NULL);
				$response->setStatusCode(404);

			} elseif ($path != $result['path']) {
				$response->set($result['path']);
				$response->setStatusCode(301);

			} else {
				$action = $this->link['action'];
				$params = array_map('urldecode', array_combine(
					$this->link['params'],
					$result['params']
				));

				if (is_string($action)) {
					$action = $this->compiler->make($action, $params, $remainder);
					$params = $remainder;
				}

				$response->set($action);
				$response->setStatusCode(200);
				$request->params->set($params);
			}

			$this->link = next($this->links);

			return TRUE;
		}


		/**
		 *
		 */
		public function wrap(Transport\Resource\Request $request, Transport\Resource\Response $response)
		{
			$candidate_handlers = array();
			$request_path       = $request->getTarget();

			foreach ($this->handlers as $handler) {
				if ($handler['status'] != $response->getStatus()) {
					continue;
				}

				if (strpos($request_path, $handler['base']) === 0) {
					$candidate_handlers[] = $handler;
				}
			}

			usort($candidate_handlers, function($a, $b) {
				return (strlen($a['base']) < strlen($b['base'])) ? -1 : 1;
			});

			$handler = reset($candidate_handlers);

			if ($handler && isset($handler['action'])) {
				$response->set($handler['action']);

				return TRUE;
			}

			return FALSE;
		}


		/**
		 *
		 */
		protected function validateConflictedAction($action, $old_action)
		{
			if ($action != $old_action) {
				throw new Flourish\ProgrammerException(
					'Previous action %s conflicts with %s.',
					$old_action,
					$action
				);
			}
		}


		/**
		 *
		 */
		protected function validateConflictedTarget($target, $old_target)
		{
			if ($target != $old_target) {
				throw new Flourish\ProgrammerException(
					'Incompatible redirect target %s does not match old target %s.',
					$old_target,
					$target
				);
			}
		}


		/**
		 *
		 */
		protected function validateConflictedTypes($type, $old_type)
		{
			if ($type != $old_type) {
				throw new Flourish\ValidationException(
					'Previous conflicting redirect type %s does not match %s.',
					$old_type,
					$type
				);
			}
		}


		/**
		 *
		 */
		private function match($pattern, $path, $loose = FALSE)
		{
			$regex = static::DELIMITER . '^' . $pattern . '$' . static::DELIMITER;

			if (preg_match($regex, $path, $matches)) {
				return [
					'path'   => array_shift($matches),
					'params' => $matches
				];
			}

			if ($loose && $path != '/') {
				return $this->match($pattern, (substr($path, -1) == '/')
					? rtrim($path, '/')
					: $path . '/'
				);
			}

			return FALSE;
		}
	}
}
