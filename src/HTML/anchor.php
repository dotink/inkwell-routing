<?php namespace Inkwell\HTML
{
	class anchor
	{
		/**
		 *
		 */
		private $router = NULL;


		/**
		 *
		 */
		public function __construct($router)
		{
			$this->router = $router;
		}


		/**
		 *
		 */
		public function __invoke($target, $data)
		{
			if ($this->router) {
				return html::out($this->router->anchor($target, $data));
			}

			return html::out(vsprintf($target, $data));
		}
	}
}
