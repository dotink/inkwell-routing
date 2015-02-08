<?php namespace Inkwell\Routing
{
	/**
	 * Simple proxy to group Collection calls under a common base
	 *
	 * This is a proxy class which calls certain methods on the Collection class using a store
	 * and common base with each call reducing the need to repeat the base in manual configuration.
	 *
	 * @copyright Copyright (c) 2015, Matthew J. Sahagian
	 * @author Matthew J. Sahagian [mjs] <msahagian@dotink.org>
	 *
	 * @license Please reference the LICENSE.md file at the root of this distribution
	 *
	 * @package Dotink\Inkwell
	 */
	class BaseGroup
	{
		/**
		 * Create a new BaseGroup
		 *
		 * @access public
		 * @param Collection $collection The collection we'll be adding to
		 * @param string $base The base route which added handlers, links, and redirects are under
		 * @return void
		 */
		public function __construct(Collection $collection, $base)
		{
			$this->collection = $collection;
			$this->base       = $base;
		}


		/**
		 * Add and error handler to the collection
		 *
		 * @access public
		 * @param string $status The status response to match
		 * @param mixed $action A resolvable action for the registered resolver
		 * @return BaseGroup The called instance for method chaining
		 */
		public function handle($status, $action)
		{
			$this->collection->handle($this->base, $status, $action);

			return $this;
		}


		/**
		 * Add a link between a route and a particular action to the collection
		 *
		 * @access public
		 * @param string $route The route to match
		 * @param mixed $action A resolvable action for the registered resolver
		 * @return BaseGroup The called instance for method chaining
		 */
		public function link($route, $action)
		{
			$this->collection->link($this->base, $route, $action);

			return $this;
		}


		/**
		 * Add a redirect between a route and a new target
		 *
		 * @access public
		 * @param string $route The route to match
		 * @param string $target The new route to seek out
		 * @param integer $type The type of redirection by code
		 * @return BaseGroup The called instance for method chaining
		 */
		public function redirect($route, $target, $type = 301)
		{
			$this->collection->redirect($this->base, $route, $target, $type);

			return $this;
		}
	}
}
