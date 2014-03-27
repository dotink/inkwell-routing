<?php namespace Dotink\Lab
{
	use Inkwell\Routing;

	return [
		/**
		 *
		 */
		'setup' => function($data, $shared)
		{
			needs($data['root'] . '/src/Engine.php');
		},


		/**
		 *
		 */
		'tests' => [

			/**
			 *
			 */
			'Instantiation' => function($data, $shared)
			{
				$shared->engine = new Routing\Engine();
			},
		]
	];
}