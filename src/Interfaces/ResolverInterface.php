<?php namespace Inkwell\Routing
{
	/**
	 *
	 *
	 * @copyright Copyright (c) 2015, Matthew J. Sahagian
	 * @author Matthew J. Sahagian [mjs] <msahagian@dotink.org>
	 *
	 * @license Please reference the LICENSE.md file at the root of this distribution
	 *
	 * @package Dotink\Inkwell
	 */
	interface ResolverInterface
	{
		/**
		 * Execute a resolved action
		 *
		 * @access public
		 * @param mixed $action The reference for the callable action
		 * @return mixed The result of the action being executed
		 */
		public function execute($action);


		/**
		 * Resolves action references
		 *
		 * The returned result should be a reference to the action which can be checked against
		 * the router's action stack.  Generally, the returned result is a valid callback with
		 * preparation work having been done.
		 *
		 * When the action needs to be executed, the reference will be passed to execute()
		 * on the resolver.
		 *
		 * @access public
		 * @param mixed $action A callable or callable representation of the action
		 * @param array $context An array of context information
		 * @return mixed An action reference executable via execute()
		 */
		public function resolve($action, Array $context);
	}
}
