<?php namespace Inkwell\Routing
{
	use Closure;
	use Exception;
	use Dotink\Flourish;

	use Inkwell\Event;
	use Inkwell\Transport\Resource\Request;
	use Inkwell\Transport\Resource\Response;

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
		 * The action execution stack starting with the entry action, ending with the current
		 *
		 * @access private
		 * @var array
		 */
		private $actions = array();


		/**
		 * The routes collection
		 *
		 * @access private
		 * @var Collection
		 */
		private $collection = NULL;


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
		public function __construct(Collection $collection = NULL, ResolverInterface $resolver = NULL)
		{
			$this->collection = $collection ?: new Collection();
			$this->resolver   = $resolver;
		}


		/**
		 *
		 */
		public function anchor($path = NULL, $params = array(), $remainder_as_query = TRUE)
		{
			switch (func_num_args()) {
				case 0:
					$path   = $this->request->getTarget();
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
			if (!func_num_args()) {
				$this->response->set(NULL);
			} elseif ($message) {
				$this->response->set($message);
			}

			throw new Flourish\ContinueException($message);
		}


		/**
		 *
		 */
		public function demit($message = NULL)
		{
			if (!func_num_args()) {
				$this->response->set(NULL);
			} elseif ($message) {
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
		public function redirect($location, $type = 303, $demit = TRUE)
		{
			$location = $location
				? $this->request->getURI()->modify($location)
				: $this->request->getURI();

			$this->response->headers->set('Location', $location);

			if (is_int($type)) {
				$this->response->setStatusCode($type);
			} else {
				$this->response->setStatus((string) $type);
			}

			if ($demit) {
				$this->demit();
			}
		}


		/**
		 *
		 */
		public function rewrite($location, $defer = TRUE)
		{
			$location = $this->request->getURI()->modify($location);

			$this->request->setURL($location);

			if ($defer) {
				$this->defer();
			}
		}


		/**
		 *
		 */
		public function run(Request $request, Response $response = NULL)
		{
			$this->response = $response ?: new Response();
			$this->request  = $request;

			$this->collection->reset();

			$this->emit('Router::begin', [
				'request'  => $this->request,
				'response' => $this->response
			]);

			//
			// Perform Rewrites
			//

			while ($this->collection->resolve($this->request, $this->response, $this->restless)) {
				try {
					$this->mapRewrite();
				} catch (Flourish\ContinueException $e) {
					$this->response->setStatusCode(404);
					continue;
				} catch (Flourish\YieldException $e) {
					break;
				}
			}

			if ($this->response->checkStatusCode(['404', '302'])) {

				//
				// No redirects or only internal redirects were found, attempt to run
				// actions.
				//

				while ($this->collection->seek($this->request, $this->response, $this->restless)) {
					try {
						$this->mapAction();
						$this->runAction();
					} catch (Flourish\ContinueException $e) {
						$this->response->setStatusCode(404);
						continue;
					} catch (Flourish\YieldException $e) {
						break;
					}
				}

				if ($this->response->getStatusCode() >= 400) {

					//
					// No viable response was found, attempt to run handlers
					//

					while ($this->collection->wrap($this->request, $this->response)) {
						try {
							$this->runHandler();
							break;

						} catch (Exception $e) {
							if (!$this->response->checkStatusCode(500)) {
								$this->response->setStatusCode(500);
								$this->response->set(NULL);
								continue;
							}

							throw $e;
						}
					}
				}
			}

			$this->emit('Router::end', [
				'request'  => $this->request,
				'response' => $this->response
			]);

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
		 * Sets the router to restless mode (will try / and non-/ URLs)
		 *
		 * @access public
		 * @param boolean $restless TRUE to try both URL forms, FALSE to only accept what is given
		 * @return void
		 */
		public function setRestless($restless)
		{
			$this->restless = $restless;
		}


		/**
		 * Executes a resolved action
		 *
		 * This function modifies the response directly and should be expected to mutate the
		 * output based on the action.
		 *
		 * @access protected
		 * @param mixed $action A callable action
		 * @return void
		 */
		protected function execute($action)
		{
			if ($action) {
				ob_start();

				$response = $this->resolver
					? $this->resolver->execute($action)
					: $action();

				if (($output = ob_get_clean()) && $this->mutable) {
					$this->response->set($output);
				} elseif (!($response instanceof Response)) {
					$this->response->set($response);
				} else {
					$this->response = $response;
				}

				return TRUE;
			}


			return FALSE;
		}


		/**
		 * Maps an action
		 */
		protected function mapAction()
		{
			if ($this->response->checkStatusCode(404)) {
				$this->defer();

			} elseif ($this->response->checkStatusCode(301)) {
				$this->redirect($this->response->get(), 301);

			} else {
				$this->actions[] = $this->resolve($this->response->get());
			}
		}


		/**
		 *
		 */
		protected function mapRewrite()
		{
			if ($this->response->checkStatusCode(404)) {
				$this->defer();

			} elseif ($this->response->checkStatusCode(302)) {
				$this->rewrite($this->response->get());

			} else {
				$target   = $this->response->get();
				$location = $this->request->getURI()->modify($target);

				$this->response->headers->set('Location', $location);

				$this->demit();
			}
		}


		/**
		 * Resolve an action using the registered resolver
		 *
		 * @access protected
		 * @param mixed $action The unresolved action
		 * @return mixed The resolved action (a valid callback)
		 * @throws Flourish\ProgrammerException If unresolved non-closure is passed without resolver
		 */
		protected function resolve($action)
		{
			if ($this->resolver) {
				return $this->resolver->resolve($action, [
					'router'   => $this,
					'request'  => $this->request,
					'response' => $this->response
				]);

			} elseif ($action instanceof Closure) {
				return $action->bindTo($this, $this);
			}

			throw new Flourish\ProgrammerException(
				'Cannot resolve non-Closure action, try registering a resolver'
			);
		}


		/**
		 *
		 */
		public function runHandler()
		{
			$action = $this->resolve($this->response->get());
			$result = $this->execute($action);
		}


		/**
		 * Runs the current action
		 *
		 * This will demit when completed causing the action chain to break.
		 *
		 * @return void
		 */
		public function runAction()
		{
			$this->emit('Router::actionBegin', [
				'request'  => $this->request,
				'response' => $this->response
			]);

			$action = $this->getAction();
			$result = $this->execute($action);

			$this->emit('Router::actionComplete', [
				'request'  => $this->request,
				'response' => $this->response
			]);

			$this->demit(NULL);
		}
	}
}
