<?php
	use Inkwell\HTML\html;

	return Affinity\Action::create(['core', 'http', 'controller'], function($app, $broker) {

		$collection = $broker->make('Inkwell\Routing\Collection');
		$router     = $broker->make('Inkwell\Routing\Engine', [
			':collection' => $collection,
			':resolver'   => isset($app['router.resolver'])
				? $app['router.resolver']
				: NULL
		]);

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
			// Redirects
			//

			foreach ($redirects as $type => $type_redirects) {
				foreach ($type_redirects as $route => $target) {
					$collection->redirect($base_url, $route, $target, $type);
				}
			}


			//
			// Handlers
			//

			foreach ($handlers as $status => $action) {
				$collection->handle($base_url, $status, $action);
			}

		}

		if (class_exists('Inkwell\HTML\html')) {
			html::add(['anchor' => new Inkwell\Routing\HTML\anchor($router)]);
		}

		$app['router']            = $router;
		$app['router.collection'] = $collection;
		$app['engine.handler']    = function($app, $broker) {
			return $app['gateway']->transport($app['router']->run(
				$app['request'],
				$app['response']
			));
		};
	});
