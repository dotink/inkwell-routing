<?php
	use Inkwell\HTML\html;

	return Affinity\Action::create(['core', 'http', 'controller'], function($app, $broker) {

		$routes = $broker->make('Inkwell\Routing\Collection');
		$router = $broker->make('Inkwell\Routing\Engine', [
			':collection' => $routes,
			':resolver'   => isset($app['router.resolver'])
				? $app['router.resolver']
				: NULL
		]);

		$router->setMutable($app['engine']->fetch('routing',  'mutable',  TRUE));
		$router->setRestless($app['engine']->fetch('routing', 'restless', TRUE));

		//
		// We register as the application engine
		//

		$app['engine.handler'] = function($app, $broker) use ($router){
			return $app['gateway']->transport($router->run($app['request'], $app['response']));
		};

		//
		// Add all our routes
		//

		foreach ($app['engine']->fetch('@routes', 'base_url') as $id => $base_url) {

			$links     = $app['engine']->fetch($id, '@routes.links',     []);
			$handlers  = $app['engine']->fetch($id, '@routes.handlers',  []);
			$redirects = $app['engine']->fetch($id, '@routes.redirects', []);

			//
			// Links
			//

			foreach ($links as $route => $action) {
				$routes->link($base_url, $route, $action);
			}


			//
			// Redirects
			//

			foreach ($redirects as $type => $type_redirects) {
				foreach ($type_redirects as $route => $target) {
					$routes->redirect($base_url, $route, $target, $type);
				}
			}


			//
			// Handlers
			//

			foreach ($handlers as $status => $action) {
				$routes->handle($base_url, $status, $action);
			}

		}

		if (class_exists('Inkwell\HTML\html')) {
			html::add(['anchor' => new Inkwell\Routing\HTML\anchor($router)]);
		}

		//
		// Set our container aliases
		//

		$app['routes']          = $routes;
		$app['router']          = $router;
		$app['router.resolver'] = NULL;
	});
