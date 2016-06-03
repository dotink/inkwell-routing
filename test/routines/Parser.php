<?php namespace Dotink\Lab
{
	use Inkwell\Routing;

	return [
		/**
		 *
		 */
		'setup' => function($data, $shared)
		{
			needs($data['root'] . '/src/Interfaces/ParserInterface.php');
			needs($data['root'] . '/src/Parser.php');
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
				$shared->parser = new Routing\Parser();
			},
		]
	];
}
