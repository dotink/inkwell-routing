<?php namespace Inkwell\Routing
{
	interface ResolverInterface
	{
		/**
		 *
		 */
		public function resolve($action, Array $context);
	}
}
