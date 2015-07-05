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
			$init_path  = __DIR__;
			$init_path != '/' && !is_file($init_path . DIRECTORY_SEPARATOR . 'init.php');
			$init_path  = realpath($init_path . DIRECTORY_SEPARATOR . '..')
		);

		if ($app = @include($init_path . DIRECTORY_SEPARATOR . 'init.php')) {

			//
			// We've got an application instance so let's run!
			//

			exit($app->run());
		}

	} catch (Exception $e) {
		if (!$app->checkExecutionMode(IW\EXEC_MODE\PRODUCTION)) {
			throw $e;
			exit(-1);
		}
	}

	header('HTTP/1.1 500 Internal Server Error');
	echo 'Something has gone terribly wrong.';
	exit(-1);
}
