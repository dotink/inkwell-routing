<?php namespace Inkwell\Routing
{
	use Closure;
	use Dotink\Flourish;
	use Inkwell\Event;
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
	class Engine implements Event\EmitterInterface
	{
		use Event\Emitter;

		/**
		 *
		 */
		private $actions = array();


		/**
		 *
		 */
		private $collection = NULL;


		/**
		 *
		 */
		private $params = array();


		/**
		 *
		 */
		private $response = NULL;


		/**
		 *
		 */
		private $request = NULL;

		/**
		 *
		 */
		private $restless = FALSE;


		/**
		 *
		 */
		public function __construct(Collection $collection, HTTP\Resource\Response $response)
		{
			$this->collection = $collection;
			$this->response   = $response;
		}


		/**
		 *
		 */
		public function anchor($path = NULL, $params = array(), $remainder_as_query = TRUE)
		{
			switch (func_num_args()) {
				case 0:
					$path   = $this->request->getUrl()->getPath();
				case 1:
					$params = $this->request->params->get();
			}

			$compiler = $this->getCollection()->getCompiler();
			$segments = explode('/', $compiler->make($path, $params, $remainder));
			$anchor   = implode('/', array_map('rawurlencode', $segments));

			if ($remainder_as_query && count($remainder)) {
				$anchor .= '?' . http_build_query($remainder, '', '&', PHP_QUERY_RFC3986);
			}

			//
			// TODO: Make anchor loop through redirects
			//

			return $anchor;
		}


		/**
 		 *
		 */
		public function defer($message = NULL)
		{
			throw new Flourish\ContinueException($message);
		}


		/**
		 *
		 */
		public function demit($message = NULL)
		{
			throw new Flourish\YieldException($message);
		}


		/**
		 *
		 */
		public function getAction()
		{
			return end($this->actions);
		}


		/**
		 *
		 */
		public function getCollection()
		{
			return $this->collection;
		}


		/**
		 *
		 */
		public function getEntryAction()
		{
			return reset($this->actions);
		}


		/**
		 *
		 */
		public function redirect($location, $demit = TRUE)
		{
			$location = $this->request->getURL()->modify($location);

			$this->response->headers->set('Location', $location);

			if ($demit) {
				$this->demit();
			}
		}


		/**
		 *
		 */
		public function rewrite($location, $defer = TRUE)
		{
			$location = $this->request->getURL()->modify($location);

			$this->request->setURL($location);

			if ($defer) {
				$this->defer();
			}
		}


		/**
		 *
		 */
		public function run(HTTP\Resource\Request $request, Closure $resolver)
		{
			$this->request  = $request;
			$this->resolver = $resolver->bindTo($this, $this);

			try {
				$this->collection->reset();
				$this->resolve();

			} catch (Flourish\YieldException $e) {
				//
				// Any yield means we should return the response right away
				//
			}

			return $this->response;
		}


		/**
		 *
		 */
		public function setMutable($mutable)
		{
			$this->mutable = $mutable;
		}


		/**
		 *
		 */
		public function setRestless($restless)
		{
			$this->restless = $restless;
		}


		/**
		 *
		 */
		protected function init($action)
		{
			if (is_string($action)) {
				if (strpos($action, '::') !== FALSE) {
					$action = explode('::', $action);
				} elseif (function_exists($action)) {
					$action = function() use ($action) { $action(); };
				} else {
					$action = FALSE;
				}
			}

			if (is_array($action)) {
				if (count($action) != 2) {
					throw new Flourish\ProgrammerException(sprintf(
						'Invalid controller callback "%s", must contain both class and method.',
						implode('::', $action)
					));
				}

				if (!class_exists($action[0])) {
					throw new Flourish\ProgrammerException(sprintf(
						'Invalid controller callback "%s", class "%s" does not exist',
						implode('::', $action),
						$action[0]
					));
				}

				if (strpos($action[1], '__') === 0) {
					throw new Flourish\ProgrammerException(sprintf(
						'Invalid controller callback "%s", method "%s" is magic or implied private',
						implode('::', $action),
						$action[1]
					));
				}

				if (!method_exists($action[0], $action[1])) {
					throw new Flourish\ProgrammerException(sprintf(
						'Invalid controller callback "%s", method "%s" does not exist on class "%s"',
						implode('::', $action),
						$action[1],
						$action[0]
					));
				}

				if (!is_callable($action)) {
					throw new Flourish\ProgrammerException(sprintf(
						'Invalid controller callback "%s", method "%s" on class "%s" is not callable',
						implode('::', $action),
						$action[1],
						$action[0]
					));
				}
			}

			$this->actions[] = call_user_func($this->resolver, $action);
		}


		/**
		 *
		 */
		protected function exec()
		{
			if ($action = $this->getAction()[0]) {
				ob_start();
				$response = call_user_func($action);
				$output   = ob_get_clean();

				if ($output && $this->mutable) {
					$this->response->set($output);
				} elseif (!($response instanceof HTTP\Resource\Response)) {
					$this->response->set($response);
				} else {
					$this->response = $response;
				}
			}
		}


		/**
		 *
		 */
		protected function resolve()
		{
			//
			// Loop through rewrites and redirects
			//

			while ($this->collection->resolve($this->request, $this->response, $this->restless)) {
				try {
					if ($this->response->checkStatusCode(404)) {
						$this->defer();

					} elseif ($this->response->checkStatusCode(302)) {
						$this->rewrite($this->response->get());

					} else {
						$target   = $this->response->get();
						$location = $this->request->getURL()->modify($target);

						$this->response->headers->set('Location', $location);

						$this->demit();
					}

				} catch (Flourish\ContinueException $e) {
					continue;
				}
			}

			//
			// Loop through links
			//

			while ($this->collection->seek($this->request, $this->response, $this->restless)) {
				try {
					if ($this->response->checkStatusCode(404)) {
						$this->defer();

					} elseif ($this->response->checkStatusCode(301)) {
						$this->redirect($this->response->get());

					} else {
						$this->init($this->response->get());

						$this->emit('Router::actionBegin', [
							'request'  => $this->request,
							'response' => $this->response
						]);

						$this->exec();

						$this->emit('Router::actionComplete', [
							'request'  => $this->request,
							'response' => $this->response
						]);

						$this->demit();
					}

				} catch (Flourish\ContinueException $e) {
					continue;
				}
			}
		}
	}
}
