<?php

namespace APIRouter;

/**
 * Routing class to match request URL's against given routes and map them to a controller action.
 */
class Router {
	/**
	 * Array that holds all Route objects
	 * @var array
	 */
	private $routes = array();

	/**
	 * Array to store named routes in, used for reverse routing.
	 * @var array
	 */
	private $namedRoutes = array();

	/**
	 * The base REQUEST_URI. Gets prepended to all route url's.
	 * @var string
	 */
	private $basePath = '';

	/**
	 * @param RouteCollection $collection
	 */
	public function __construct(RouteCollection $collection) {
		$this->routes = $collection;
	}

	/**
	 * Set the base _url - gets prepended to all route _url's.
	 *
	 * @param $basePath
	 */
	public function setBasePath($basePath) {
		$this->basePath = (string) $basePath;
	}

	/**
	 * Matches the current request against mapped routes
	 */
	public function matchCurrentRequest() {
		$requestMethod = (
			isset($_POST['_method'])
			&& ($_method = strtoupper($_POST['_method']))
			&& in_array($_method, array('PUT', 'DELETE'))
		) ? $_method : $_SERVER['REQUEST_METHOD'];

		$requestUrl = $_SERVER['REQUEST_URI'];

		// strip GET variables from URL
		if ( ($pos = strpos($requestUrl, '?')) !== false ) {
			$requestUrl = substr($requestUrl, 0, $pos);
		}

		$requestMethod = strtolower($requestMethod);

		return $this->match($requestUrl, $requestMethod);
	}

	/**
	 * Match given request _url and request method and see if a route has been defined for it
	 * If so, return route's target
	 * If called multiple times
	 *
	 * @param string $requestUrl
	 * @param string $requestMethod
	 *
	 * @return bool
	 */
	public function match($requestUrl, $requestMethod = 'get') {
		foreach ($this->routes->all() as $routes) {

			// compare server request method with route's allowed http methods
			if ( !in_array($requestMethod, (array) $routes->getMethods()) ) {
				continue;
			}

			// check if request _url matches route regex. if not, return false.
			if ( !preg_match("@^" . $this->basePath . '/' . $routes->getRegex() . "*$@i", $requestUrl, $matches) ) {
				continue;
			}

			$params = array();

			if ( preg_match_all('/:([\w-%]+)/', $routes->getUrl(), $argument_keys) ) {

				// grab array with matches
				$argument_keys = $argument_keys[1];

				// loop trough parameter names, store matching value in $params array
				foreach ($argument_keys as $key => $name) {
					if ( isset($matches[$key + 1]) ) {
						$params[$name] = $matches[$key + 1];
					}
				}

			}

			$routes->setParameters($params);
			$routes->dispatch();

			return $routes;
		}

		return false;
	}


	/**
	 * Reverse route a named route
	 *
	 * @param       $routeName
	 * @param array $params Optional array of parameters to use in URL
	 *
	 * @throws Exception
	 *
	 * @internal param string $route_name The name of the route to reverse route.
	 * @return string The url to the route
	 */
	public function generate($routeName, array $params = array()) {
		// Check if route exists
		if ( !isset($this->namedRoutes[$routeName]) ) {
			throw new \Exception("No route with the name $routeName has been found.");
		}

		$route = $this->namedRoutes[$routeName];
		/* @var $route Route */
		$url = $route->getUrl();

		// replace route url with given parameters
		if ( $params && preg_match_all('/:(\w+)/', $url, $param_keys) ) {
			// grab array with matches
			$param_keys = $param_keys[1];

			// loop trough parameter names, store matching value in $params array
			foreach ($param_keys as $key) {
				if ( isset($params[$key]) ) {
					$url = preg_replace('/:(\w+)/', $params[$key], $url, 1);
				}
			}
		}

		return $url;
	}


	/**
	 * Create routes by array, and return a Router object
	 *
	 * @param array $config
	 *
	 * @return Router
	 */
	public static function load(array $config) {
		$collection = new RouteCollection();
		foreach ($config as $name => $route) {
			$arActions = explode(' ', $name);
			$arActions[0] = strtolower($arActions[0]);
			switch ($arActions[0]) {
				case 'resources':
					$collection->attachRoute(
						new Route(
							$route, array(
								'_controller' => $route . '#index',
								'method'      => 'get'
							)
						)
					);
					$collection->attachRoute(
						new Route(
							$route . '/new', array(
								'_controller' => $route . '#new',
								'method'      => 'get'
							)
						)
					);
					$collection->attachRoute(
						new Route(
							$route, array(
								'_controller' => $route . '#create',
								'method'      => 'post'
							)
						)
					);
					$collection->attachRoute(
						new Route(
							$route . '/:id', array(
								'_controller' => $route . '#show',
								'method'      => 'get'
							)
						)
					);
					$collection->attachRoute(
						new Route(
							$route . '/:id/edit', array(
								'_controller' => $route . '#edit',
								'method'      => 'get'
							)
						)
					);
					$collection->attachRoute(
						new Route(
							$route . '/:id', array(
								'_controller' => $route . '#update',
								'method'      => 'patch'
							)
						)
					);
					$collection->attachRoute(
						new Route(
							$route . '/:id', array(
								'_controller' => $route . '#update',
								'method'      => 'put'
							)
						)
					);
					$collection->attachRoute(
						new Route(
							$route . '/:id', array(
								'_controller' => $route . '#destroy',
								'method'      => 'delete'
							)
						)
					);
					break;
				case 'get':
				case 'post':
				case 'put':
				case 'patch':
				case 'delete':
					if ( !empty($arActions[1]) ) {
						$collection->attachRoute(
							new Route(
								$arActions[1], array(
									'_controller' => $route,
									'method'      => $arActions[0]
								)
							)
						);
					}
					break;
			}
		}

		$router = new Router($collection);

		return $router;
	}
}
