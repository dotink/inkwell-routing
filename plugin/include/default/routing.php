<?php

	return Affinity\Action::create(['core', 'response'], function($app, $container) {

		$router     = $container->make('Inkwell\Routing\Engine');
		$collection = $router->getCollection();

		$router->setMutable($app['engine']->fetch('routing',  'mutable',  TRUE));
		$router->setRestless($app['engine']->fetch('routing', 'restless', TRUE));

		foreach ($app['engine']->fetch('@routes', 'base_url') as $id => $base_url) {

			$links     = $app['engine']->fetch($id, '@routes.links',     []);
			$handlers  = $app['engine']->fetch($id, '@routes.handlers',  []);
			$redirects = $app['engine']->fetch($id, '@routes.redirects', []);

			//
			// Links
			//

			foreach ($links as $route => $action) {
				$collection->link($base_url, $route, $action);
			}

			//
			// Handlers
			//

			foreach ($handlers as $status => $action) {
				$collection->handle($base_url, $status, $action);
			}

			//
			// Redirects
			//

			foreach ($redirects as $type => $redirects) {
				foreach ($redirects as $route => $target) {
					$collection->redirect($base_url, $route, $target, $type);
				}
			}
		}

		$app['router'] = $router;
	});
