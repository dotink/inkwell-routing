<?php namespace Inkwell
{
	use Exception;
	use Closure;
	use IW;

	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);

	try {

		//
		// Track backwards until we discover our includes directory.  The only file required
		// to be in place for this is includes/init.php which should return our application
		// instance.
		//

		for (

			//
			// Initial assignment
			//

			$init_path = __DIR__;

			//
			// While Condition
			//

			$init_path && !is_file($init_path . DIRECTORY_SEPARATOR . 'init.php');

			//
			// Modifier
			//

			$init_path = realpath($init_path . DIRECTORY_SEPARATOR . '..')
		);


		$app = include($init_path . DIRECTORY_SEPARATOR . 'init.php');

		$app->run(function($app, $broker) {
			$app['gateway']->transport(

				//
				// Running the router will return the response for transport
				//

				$app['response'] = $app['router']->run($app['request'], $app['router.resolver'])
			);
		});

	} catch (Exception $e) {

		if ($app->checkExecutionMode(IW\EXEC_MODE\PRODUCTION)) {
			header('HTTP/1.1 500 Internal Server Error');
			echo 'Something has gone terribly wrong.';
			exit(-1);
		}

		throw $e;
		exit(-1);
	}
}
