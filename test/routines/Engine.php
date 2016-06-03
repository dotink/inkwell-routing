<?php namespace Dotink\Lab
{
	use Inkwell\Routing;
	use Dotink\Parody\Mime;

	return [
		/**
		 *
		 */
		'setup' => function($data, $shared)
		{
			Mime::define('Inkwell\Routing\Collection')->create();

			needs($data['root'] . '/test/shims/EmitterInterface.php');
			needs($data['root'] . '/test/shims/Emitter.php');
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
