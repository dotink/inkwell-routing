<?php namespace Inkwell\Routing
{
	use Dotink\Flourish;

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
	class Collection implements CollectionInterface
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
		 * @param string $base_path The base path for all the routes
		 * @param string $status The status string (see HTTP namespace)
		 * @param mixed $action The action to call on error
		 * @return void;
		 */
		public function handle($base_path, $status, $action)
		{
			$base_path = rtrim($base_path, '/');
			$hash     = md5($base_path . $error);

			if (isset($this->handlers[$hash])) {
				throw new Flourish\ProgrammerException(
					'The base URL %s already has a handler registered for status %s.',
					$base_path,
					$status
				);
			}

			$this->handlers[$hash] = [
				'base_path' => $base_path,
				'action'   => $action,
				'status'   => $status
			];
		}


		/**
		 *
		 */
		public function link($base_path, $route, $action)
		{
			$base_path = rtrim($base_path, '/');
			$route    = ltrim($route, '/');
			$pattern  = $this->parser->regularize(
				$base_path . '/' . $route,
				static::DELIMITER,
				$params = array()
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
				'base_path' => $base_path,
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
		public function redirect($base_path, $route, $target, $type = 301)
		{
			$base_path = rtrim($base_path, '/');
			$route    = ltrim($route, '/');
			$pattern  = $this->parser->regularize($base_path . '/' . $route, $params);

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
				'base_path' => $base_path,
				'params'   => $params,
				'type'     => $type
			];
		}


		/**
		 *
		 */
		public function reset()
		{
			$this->link = NULL;

			reset($this->links);
		}


		/**
		 *
		 */
		public function seek(EngineInterface $engine, CompilerInterface $compiler)
		{
			if (!$this->link = ($this->link ? next($this->links) : current($this->links))) {
				return NULL;
			}

			$pattern = key($this->links);
			$matches = $this->match(
				static::DELIMITER . '^' . $pattern . '$' . static::DELIMITER,
				$engine->getRequestPath(),
				$engine->isRestless()
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

				$engine->setAction($action);
				$engine->setParams($params);

				return TRUE;
			}

			return FALSE;
		}


		/**
		 *
		 */
		public function rewrite(EngineInterface $engine, CompilerInterface $compiler)
		{
			if (!count($this->redirects)) {
				return NULL;
			}

			foreach ($this->redirects as $pattern => $redirect) {
				$matches = $this->match(
					static::DELIMITER . '^' . $pattern . '$' . static::DELIMITER,
					$engine->getRequestPath(),
					$engine->isRestless()
				);

				if ($matches) {
					array_shift($matches);

					$params = array_combine($redirect['params'], $matches);
					$path   = $compiler->make($redirect['target'], $params, $remainder);
					$type   = $redirect['type'];

					$engine->setRequestPath($path);
					$engine->setParams($remainder);

					return $type ?: $this->rewrite($engine, $path);
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
		private function match($regex, $path, $require_canonical = TRUE)
		{
			if (preg_match($regex, $path, $matches)) {
				return $matches;
			}

			if (!$require_canonical) {
				$path = (substr($path, -1) == '/')
					? rtrim($path, '/')
					: $path . '/';

				if (preg_match($regex, $path, $matches)) {
					return $matches;
				}
			}

			return FALSE;
		}
	}
}
