<?php

	use IW\HTTP;

	return Affinity\Config::create(['providers', 'routes'], [

		//
		// Whether or not we allow action output to override returned value.  This means that
		// you can `echo` or `var_dump` inside a routed action and instead of using the returned
		// value from the action, any output generated will be the content of the response.
		//

		'mutable' => TRUE,

		//
		// Whether or not we should attempt to try URLs with and without trailing slashes
		//

		'restless' => TRUE,

		//
		// The default word separator for url components
		//

		'word_separator' => '-',

		//
		// @providers allows you to wire together dependencies
		//

		'@providers' => [

			//
			// The provider mapping lists concrete class providers for given interfaces, the
			// interface is the key, while the class is the value.
			//

			'mapping' => [
				'Inkwell\Routing\ParserInterface'   => 'Inkwell\Routing\Parser',
				'Inkwell\Routing\CompilerInterface' => 'Inkwell\Routing\Compiler'
			]
		],

		//
		// Global routing configuration
		//

		'@routes' => [

			//
			// The base URL for all configured anchors, handlers, and redirects in this
			// context
			//

			'base_url' => '/',

			//
			//
			//

			'links' => [

			],

			//
			//
			//

			'handlers' => [

			],

			//
			//
			//

			'redirects' => [

			]
		]
	]);
