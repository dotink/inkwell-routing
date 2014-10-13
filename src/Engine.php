<?php namespace Inkwell\Routing
{
	use Closure;
	use Dotink\Flourish;
	use Inkwell\RouterInterface;
	use Inkwell\Transport\RequestInterface;
	use Inkwell\Transport\ResponseInterface;
	use Inkwell\Event;

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
	class Engine implements EngineInterface, Event\EmitterInterface
	{
		use Event\Emitter;

		/**
		 *
		 */
		private $action = NULL;


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
		public function __construct(CollectionInterface $collection, ResponseInterface $response, CompilerInterface $compiler)
		{
			$this->collection = $collection;
			$this->response   = $response;
			$this->compiler   = $compiler;
		}


		/**
		 *
		 */
		public function anchor($path = NULL, $params = array(), $remainder_as_query = TRUE)
		{
			switch (count(func_num_args())) {
				case 0:
					$path   = $this->request->getUrl()->getPath();
				case 1:
					$params = $this->request->params->getAll();
			}

			$segments = explode('/', $this->compiler->make($path, $params, $remainder));
			$anchor   = implode('/', array_map('rawurlencode', $segments));

/*
			$old      = [
				'path'   => $this->request->getUrl()->getPath(),
				'params' => $this->getParams()
			];
*/
			if ($remainder_as_query) {
				$anchor .= '?' . http_build_query($remainder, '', '&', PHP_QUERY_RFC3986);
			}

			return $anchor;
		}


		/**
 		 *
		 */
		public function defer($message)
		{
			throw new Flourish\ContinueException($message);
		}


		/**
		 *
		 */
		public function redirect($location, $status_code = 303, $yield = TRUE)
		{
			$location = $this->request->getURL()->modify($location);

			$this->response->headers->set('Location', $location);
			$this->response->setStatusCode($status_code);

			if ($yield) {
				$this->quit();
			}
		}


		/**
		 *
		 */
		public function run(RequestInterface $request, Callable $resolver)
		{
			$this->request  = $request;
			$this->resolver = $resolver;

			$this->collection->reset($this->restless);

			if ($status_code = $this->collection->rewrite($request, $this->compiler)) {
				$this->redirect($this->anchor(), $status_code, FALSE);

			} else {

				$original_url = $request->getUrl();

				do {
					$action = $this->collection->seek($request, $this->compiler);

					if ($action === NULL) {

						//
						// No more actions left
						//

						break;
					}

					if ($action === FALSE) {

						//
						// Action did not match
						//

						continue;
					}

					if ($original_url->getPath() != $request->getURL()->getPath()) {
						$this->redirect($this->anchor(), 301, FALSE);
						break;
					}

					try {
						$this->prepareAction($action);
						$this->captureResponse($action);
						break;

					} catch (Flourish\ContinueException $e) {
						continue;

					} catch (Flourish\YieldException $e) {
						break;
					}

				} while (TRUE);
			}


			return $this->response;
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
		public function setMutable($mutable)
		{
			$this->mutable = $mutable;
		}


		/**
		 *
		 */
		public function quit($message = NULL)
		{
			throw new Flourish\YieldException($message);
		}


		/**
		 *
		 */
		protected function captureResponse()
		{
			$this->response->setStatusCode(200);

			$this->emit('Router::actionBegin', [
				'request'  => $this->request,
				'response' => $this->response
			]);

			ob_start();
			$response = call_user_func($this->resolver, $this->action);
			$output   = ob_get_clean();

			$this->response->setBody(!($output && $this->mutable)
				? $response
				: $output
			);

			$this->emit('Router::actionComplete', [
				'request'  => $this->request,
				'response' => $this->response
			]);
		}


		/**
		 *
		 */
		protected function prepareAction($action)
		{
			$this->action = $action;

			if (is_string($this->action)) {
				if (strpos($this->action, '::') !== FALSE) {
					$this->action = explode('::', $this->action);

				} elseif (!is_callable($this->action)) {
					throw new Flourish\ContinueException();
				}
			}

			if (is_array($this->action)) {
				if (count($this->action) != 2) {
					throw new Flourish\ContinueException();
				}

				if (!class_exists($this->action[0])) {
					throw new Flourish\ContinueException();
				}

				if (strpos($this->action[1], '__') == 0) {
					throw new Flourish\ContinueException();
				}

				if (!method_exists($this->action[0], $this->action[1])) {
					throw new Flourish\ContinueException();
				}

				if (!is_callable($this->action)) {
					throw new Flourish\ContinueException();
				}

			} elseif (!$this->action instanceof Closure) {
				throw new Flourish\ContinueException();
			}
		}
	}
}
