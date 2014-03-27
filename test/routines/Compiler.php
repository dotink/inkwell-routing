<?php namespace Dotink\Lab
{
	use Inkwell\Routing;

	return [
		/**
		 *
		 */
		'setup' => function($data, $shared)
		{
			needs($data['root'] . '/src/Compiler.php');
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
				$shared->compiler = new Routing\Compiler();
			},


			/**
			 *
			 */
			'Method - makeUpperCamelCase' => function($data, $shared)
			{
				assert('Inkwell\Routing\Compiler::makeUpperCamelCase')
					-> using  ($shared->compiler)
					-> with   ('foo-bar')
					-> equals ('FooBar')
				;
			},


			/**
			 *
			 */
			'Method - makeLowerCamelCase' => function($data, $shared)
			{
				assert('Inkwell\Routing\Compiler::makeLowerCamelCase')
					-> using  ($shared->compiler)
					-> with   ('foo-bar')
					-> equals ('fooBar')
				;				
			},


			/**
			 *
			 */
			'Method - makeUnderScored' => function($data, $shared)
			{
				assert('Inkwell\Routing\Compiler::makeUnderScored')
					-> using  ($shared->compiler)
					-> with   ('foo-bar')
					-> equals ('foo_bar')
				;				
			},


			/**
			 *
			 */
			'Method - makeWebSafe' => function($data, $shared)
			{
				assert('Inkwell\Routing\Compiler::makeWebSafe')
					-> using  ($shared->compiler)

					-> with   ('fooBar')
					-> equals ('foo-bar')

					-> with   ('This is a completely strange test, but I like it!')
					-> equals ('this-is-a-completely-strange-test-but-i-like-it')

					-> with   ('AreYouGoing-to-Scarborough fair... (with me?)')
					-> equals ('are-you-going-to-scarborough-fair-with-me')
				;				
			},


			/**
			 *
			 */
			'Method - make' => function($data, $shared)
			{
				assert($shared->compiler->make(
						'[uc:class]::[lc:method]',
						['class' => 'foo-bar', 'method' => 'test-the-best', 'left' => 'over'],
						$remainder
				), EXACTLY)->equals('FooBar::testTheBest');

				assert($remainder)->equals(['left' => 'over']);
			}

		]
	];
}