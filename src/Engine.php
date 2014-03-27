<?php namespace Inkwell\Routing
{
	use Closure;
	use Dotink\Flourish;
	use Inkwell\RouterInterface;
	use Inkwell\RequestInterface;
	use Inkwell\ResponseInterface;
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
		private $requestPath = NULL;


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
			switch (count(func_num_args)) {
				case 0:
					$path = $this->getRequestPath();
				case 2:
					$params = $this->getParams();
			}

			$segments = explode('/', $this->compiler->make($path, $params, $remainder));
			$request  = implode('/', array_map('rawurlencode', $segments));

			if ($remainder_as_query) {
				$request .= '?' . http_build_query($remainder, '', '&', PHP_QUERY_RFC3986);
			}

			return $request;
		}


		/**
		 *
		 */
		public function checkRequestPath($path)
		{
			return $path == $this->requestPath;
		}


		/**
		 *
		 */
		public function isRestless()
		{
			return $this->restless;
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
		public function getRequestPath()
		{
			return $this->requestPath;
		}


		/**
		 *
		 */
		public function run(RequestInterface $request, Callable $resolver)
		{
			$this->request  = $request;
			$this->resolver = $resolver;

			$this->setRequestPath($request->getURL()->getPath());
			$this->collection->reset();

			if ($status = $this->collection->rewrite($this, $this->compiler)) {
				$this->request->redirect($this->anchor(), $status);
			}

			do {
				if (!$result = $this->collection->seek($this, $this->compiler)) {
					continue;
				}

				if (!$this->checkRequestPath($request->getURL()->getPath())) {
					$this->request->redirect($this->anchor(), 301);
				}

				try {
					$this->prepareAction();

					foreach ($this->params as $param => $value) {
						$this->request->set($param, $value);
					}

					$this->captureResponse();

				} catch (Flourish\ContinueException $e) {
					foreach (array_keys($this->params) as $param) {
						$this->request->unset($param);
					}

					continue;

				} catch (Flourish\YieldException $e) {
					break;
				}
			} while ($result !== NULL);

			return $this->response;
		}


		/**
		 *
		 */
		public function setAction($action)
		{
			$this->action = $action;
		}


		/**
		 *
		 */
		public function setParams(Array $params)
		{
			$this->params = $params;
		}


		/**
		 *
		 */
		public function setRequestPath($path)
		{
			$this->requestPath = $path;
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
		protected function captureResponse()
		{
			ob_start();

			$this->emit('Router::actionBegin', [
				'request'  => $this->request,
				'response' => $this->response
			]);

			$response = call_user_func($this->resolver, $this->action);
			$output   = ob_get_clean();

			if ($output) {
				$response = $this->mutable
					? $this->response('OK', $output)
					: $this->response($this->response->getStatus(), $output);

			} else {
				$response = $this->response->resolve($response);
			}

			$this->response = $response;

			$this->emit('Router::actionComplete', [
				'request'  => $this->request,
				'response' => $this->response
			]);
		}


		/**
		 *
		 */
		protected function prepareAction()
		{
			if (is_string($this->action)) {
				if (strpos($this->action, '::') !== FALSE) {
					$this->action = explode('::', $this->action);

				} elseif (!is_callable($action)) {
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