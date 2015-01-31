<?php namespace Inkwell\Routing
{
	use Closure;
	use Inkwell\HTTP;
	use Inkwell\Event;
	use Dotink\Flourish;

	/**
	 * The main routing engine which runs routing operations over a collection
	 *
	 * @copyright Copyright (c) 2015, Matthew J. Sahagian
	 * @author Matthew J. Sahagian [mjs] <msahagian@dotink.org>
	 *
	 * @license Please reference the LICENSE.md file at the root of this distribution
	 *
	 * @package Dotink\Inkwell
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
		private $resolver = NULL;


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

			$compiler = $this->collection->getCompiler();
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
			if (func_num_args()) {
				$this->response->set($message);
			}

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
		public function getEntryAction()
		{
			return reset($this->actions);
		}


		/**
		 *
		 */
		public function isAction($action)
		{
			return $this->getAction() == $action;
		}


		/**
		 *
		 */
		public function isEntryAction($action)
		{
			return $this->getEntryAction() == $action;
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
		public function run(HTTP\Resource\Request $request, ResolverInterface $resolver = NULL)
		{
			$this->request  = $request;
			$this->resolver = $resolver;

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
		protected function exec()
		{
			$this->emit('Router::actionBegin', [
				'request'  => $this->request,
				'response' => $this->response
			]);

			if ($action = $this->getAction()) {
				ob_start();
				$response = $action();
				$output   = ob_get_clean();

				if ($output && $this->mutable) {
					$this->response->set($output);
				} elseif (!($response instanceof HTTP\Resource\Response)) {
					$this->response->set($response);
				} else {
					$this->response = $response;
				}
			}

			$this->emit('Router::actionComplete', [
				'request'  => $this->request,
				'response' => $this->response
			]);
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
						$action = $this->response->get();

						if ($this->resolver) {
							$this->actions[] = $this->resolver->resolve($action, [
								'router'   => $this,
								'request'  => $this->request,
								'response' => $this->response
							]);

						} elseif (!($action instanceof Closure)) {
							throw new Flourish\ProgrammerException(
								'Cannot execute non-Closure routing action, no resolver'
							);

						} else {
							$this->actions[] = $action->bindTo($this, $this);
						}

						$this->exec();
						$this->demit();
					}

				} catch (Flourish\ContinueException $e) {
					continue;
				}
			}
		}
	}
}
