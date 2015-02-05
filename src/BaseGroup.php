<?php namespace Inkwell\Routing
{
	use Dotink\Flourish;
	use Inkwell\HTTP;

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
	class BaseGroup
	{
		/**
		 *
		 */
		public function __construct(Collection $collection, $base_url = '/')
		{
			$this->collection = $collection;
			$this->baseUrl    = $base_url;
		}


		/**
		 *
		 */
		public function handle($status, $action)
		{
			$this->collection->handle($this->baseUrl, $status, $action);

			return $this;
		}


		/**
		 *
		 */
		public function link($route, $action)
		{
			$this->collection->link($this->baseUrl, $route, $action);

			return $this;
		}


		/**
		 *
		 */
		public function redirect($route, $target, $type = 301)
		{
			$this->collection->redirect($this->baseUrl, $route, $target, $type);

			return $this;
		}
	}
}
