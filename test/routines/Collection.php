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
			Mime::define('Inkwell\Routing\Parser');
			Mime::define('Inkwell\Routing\Compiler');

			$shared->parser    = Mime::create('Inkwell\Routing\Parser');
			$shared->pcompiler = Mime::create('Inkwell\Routing\Compiler');
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
				needs($data['root'] . '/src/Collection.php');

				$shared->collection = new Routing\Collection(
					$shared->parser->resolve()
				);
			},
		]
	];
}
