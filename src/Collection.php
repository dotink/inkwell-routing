<?php namespace Inkwell\Routing
{
	use Dotink\Flourish;
	use Inkwell\HTTP;

	/**
	 * Collection class responsible for aggregating and mapping routes to actions
	 *
	 * @copyright Copyright (c) 2012, Matthew J. Sahagian
	 * @author Matthew J. Sahagian [mjs] <gent@dotink.org>
	 *
	 * @license Please reference the LICENSE.txt file at the root of this distribution
	 *
	 * @package Inkwell\Routing
	 */
	class Collection
	{
		const DELIMITER = '#';


		/**
		 *
		 */
		protected $handlers = array();


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
		public function __construct(Parser $parser)
		{
			$this->parser = $parser;
		}


		/**
		 * Handles an error with an action in the routes collection
		 *
		 * @access public
		 * @param string $base_url The base path for all the routes
		 * @param string $status The status string (see HTTP namespace)
		 * @param mixed $action The action to call on error
		 * @return void;
		 */
		public function handle($base_url, $status, $action)
		{
			$base_url = rtrim($base_url, '/');
			$hash     = md5($base_url . $error);

			if (isset($this->handlers[$hash])) {
				throw new Flourish\ProgrammerException(
					'The base URL %s already has a handler registered for status %s.',
					$base_url,
					$status
				);
			}

			$this->handlers[$hash] = [
				'base_url' => $base_url,
				'action'   => $action,
				'status'   => $status
			];
		}


		/**
		 *
		 */
		public function link($base_url, $route, $action)
		{
			$base_url = rtrim($base_url, '/');
			$route    = ltrim($route, '/');
			$pattern  = $this->parser->regularize(
				$base_url . '/' . $route,
				static::DELIMITER,
				$params
			);

			if (isset($this->links[$pattern])) {
				try {
					$this->validateConflictedAction($action, $this->links[$pattern]['action']);

				} catch (Flourish\ValidationException $e) {
					throw new Flourish\ProgrammerException(
						'%s  Cannot add conflicting route %s.',
						$e->getMessage(),
						$route
					);
				}
			}

			$this->links[$pattern] = [
				'base_url' => $base_url,
				'action'   => $action,
				'params'   => $params
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
		public function redirect($base_url, $route, $target, $type = 301)
		{
			$base_url = rtrim($base_url, '/');
			$route    = ltrim($route, '/');
			$pattern  = $this->parser->regularize($base_url . '/' . $route, $params);

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
				'base_url' => $base_url,
				'params'   => $params,
				'type'     => $type
			];
		}


		/**
		 *
		 */
		public function reset($loose_matching)
		{
			$this->link          = NULL;
			$this->looseMatching = $loose_matching;

			reset($this->links);
		}


		/**
		 *
		 */
		public function seek(HTTP\Resource\Request $request, CompilerInterface $compiler)
		{
			$this->link = $this->link === NULL
				? current($this->links)
				: next($this->links);

			if (!$this->link) {
				return NULL;
			}

			$pattern = key($this->links);
			$matches = $this->match(
				static::DELIMITER . '^' . $pattern . '$' . static::DELIMITER,
				$request
			);

			if ($matches) {
				array_shift($matches);

				$action = $this->link['action'];
				$params = array_combine($this->link['params'], $matches);
				$params = array_map('urldecode', $params);

				if (is_string($action)) {
					$action = $compiler->make($action, $params, $remainder);
					$params = $remainder;
				}

				$request->params->set($params);

				return $action;
			}

			return FALSE;
		}


		/**
		 *
		 */
		public function rewrite(HTTP\Resource\Request $request, CompilerInterface $compiler)
		{
			if (!count($this->redirects)) {
				return NULL;
			}

			foreach ($this->redirects as $pattern => $redirect) {
				$matches = $this->match(
					static::DELIMITER . '^' . $paFtern . '$' . static::DELIMITER,
					$request
				);

				if ($matches) {
					array_shift($matches);

					$params = array_combine($redirect['params'], $matches);
					$path   = $compiler->make($redirect['target'], $params, $remainder);
					$type   = $redirect['type'];

					$request->params->set($params);

					return $type ?: $this->rewrite($request, $compiler);
				}
			}

			return FALSE;
		}


		/**
		 *
		 */
		protected function validateConflictedAction($action, $old_action)
		{
			if ($action != $old_action) {
				throw new Flourish\ValidationException(
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
		private function match($regex, $request)
		{
			$url  = $request->getURL();
			$path = $url->getPath();

			if (preg_match($regex, $path, $matches)) {
				return $matches;
			}

			if ($this->looseMatching) {
				$path = (substr($path, -1) == '/')
					? rtrim($path, '/')
					: $path . '/';

				if (preg_match($regex, $path, $matches)) {

					$request->setUrl($url->modify(['path' => $path]));


					return $matches;
				}
			}

			return FALSE;
		}
	}
}
