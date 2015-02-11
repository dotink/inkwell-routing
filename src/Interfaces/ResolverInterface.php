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
		 * Resolves executable actions and references
		 *
		 * The returned reference should be in the form of an array with the first element
		 * representing a specific callable action.  This should be in the form of a valid
		 * callback which is executable by calling `$action()` alone.  This allows for
		 * closures, invoke-able classes, function names (as strings), etc.
		 *
		 * @access public
		 * @param mixed $action A callable or callable representation of the action
		 * @param array $context An array of context information
		 * @return array An action reference
		 */
		public function resolve($action, Array $context);
	}
}
