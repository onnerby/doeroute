<?php

namespace Doe
{

	/**
	 * A router using a tree of closures to find the right route
	 *
	 *   $router = new \Doe\Router(['read', 'create']);
	 *   $router->path('path', function($router) {
	 *       $router->path('subpath1', 'read', function ($router) { return "This is route /path/subpath1"; });
	 *       $router->path('subpath2', ['read', 'create'], function ($router) { return "This is route /path/subpath2"; });
	 *   });
	 *
	 * Paths can also have variables defined by regex patterns. Sub-patterns are passed to route handlers as
	 * arguments.
	 *
	 *   $router = new \Doe\Router(['read', 'create']);
	 *   $router->path('path', function($router) {
	 *       $router->pathVariable('/^([a-zA-Z0-9]+)$/', function ($router, $arg1) { 
	 *           $router->path('subpath2', ['read', 'create'], function ($router, $arg1) { return "This is route /path/" . $arg1 . "/subpath2"; });
	 *       });
	 *   });
	 *
	 * Filters can be run before or after the action. They are run in the order they are registered.
	 * If any before-filter returns a value (not-null) processing is aborted and that value is the final result.
	 * After-filters are run in a chain, each filter passing the result on to the next, and finally out the end.
	 *
	 * Filters can be nested!
	 *
	 * Route with {@see self::route()}:
	 *
	 *   $router->route($verb, $path)
	 *
	 */
	class Router
	{
		/** @var array Used when grouping routes */
		private $filterContextStack = [];
		/** @var array Verbs used by router */
		private $verbs = [];
		/** @var array Temporary possible subpaths */
		private $subpaths = [];
		/** @var array Temporary possible subpatterns */
		private $subpatterns = [];
		/** @var callable */
		public $pathSplitter = null;

		/**
		 * Create a router
		 *
		 * @param string[] $verbs ([])
		 * @return self
		 */
		public function __construct($verbs = [])
		{
			$this->verbs = $verbs;
			$this->pathSplitter = function ($path) {
				return explode('/', trim($path, '/'));
			};
		}


		/*
		|--------------------------------------------------------------------------
		| Route registration
		|--------------------------------------------------------------------------
		*/

		/**
		 * Add a possible path to the route
		 *
		 * @param string|array $subpath exact paths
		 * @param string|array $verbs (optional)
		 * @param callable $callback Callback executed if path match
		 * @return Router for chaining
		 */
		public function path(string $subpath) : Router
		{
			$args = func_get_args();
			$callback = array_pop($args);
			$verbs = (count($args) == 2 && $args[1] !== false) ? (is_array($args[1]) ? $args[1] : [$args[1]]) : false;
			$filters = $this->filterContextStack;
			$subpaths = is_array($subpath) ? $subpath : [$subpath];

			foreach ($subpaths as $path) {
				$this->subpaths[$path] = $this->createPath($callback, $verbs, $filters);
			}

			return $this;
		}

		/**
		 * Add a possible empty path to the route
		 *
		 * @param string|array $verbs (optional)
		 * @param callable $callback Callback executed if path match
		 * @return Router for chaining
		 */
		public function pathEmpty() : Router
		{
			$args = func_get_args();
			$callback = array_pop($args);
			$verbs = (count($args) == 1 && $args[0] !== false) ? (is_array($args[0]) ? $args[0] : [$args[0]]) : false;
			return $this->path(':empty:', $verbs, $callback);
		}

		/**
		 * Add a possible callback for no path found
		 *
		 * @param callable $callback Callback executed if path match
		 * @return Router for chaining
		 */
		public function pathNotFound($callback) : Router
		{
			return $this->path(':notfound:', $callback);
		}

		/**
		 * Add a variable path to the route
		 *
		 * @param string $pattern Regexp pattern
		 * @param string|array $verbs (optional)
		 * @param callable $callback Callback executed if path match
		 * @return Router for chaining
		 */
		public function pathVariable(string $pattern) : Router
		{
			$args = func_get_args();
			$callback = array_pop($args);
			$verbs = (count($args) == 2 && $args[1] !== false) ? (is_array($args[1]) ? $args[1] : [$args[1]]) : false;
			$filters = $this->filterContextStack;

			$this->subpatterns[] = $this->createPath($callback, $verbs, $filters, $pattern);
			return $this;
		}

		/*
		|--------------------------------------------------------------------------
		| Filtering
		|--------------------------------------------------------------------------
		*/

		/**
		 * Create a filter context for a bunch of routes
		 *
		 * Callback gets this router as sole argument.
		 *
		 * Filters have filterInfo as argument.
		 *
		 * @param callable $filterCallback Filters to run before routes
		 * @param callable $routeCallback Add your routes in this callback
		 * @return Router for chaining
		 */
		public function filter($filterCallback, $routeCallback) : Router
		{
			$this->filterContextStack[] = $filterCallback;
			call_user_func($routeCallback, $this);
			array_pop($this->filterContextStack);
			return $this;
		}

		/*
		|--------------------------------------------------------------------------
		| Routing
		|--------------------------------------------------------------------------
		*/

		/**
		 * Route
		 * Note: You can only call this function once
		 *
		 * @param string $verb
		 * @param string $path
		 * @return string
		 */
		public function route(string $verb, string $path) : string
		{
			$fullpath = call_user_func_array($this->pathSplitter, [$path]);
			$variables = [];
			$subpath = '';

			foreach ($fullpath as $subpath) {
				$route = $this->subpaths[$subpath] ?? null;

				if ($route && $this->matchVerb($route, $verb)) {
					$subpath = '';
					if ($result = $this->callRoute($route, $verb, $variables)) {
						return $result;
					}
				} else {
					// Check for patterns
					foreach ($this->subpatterns as $route) {
						$match = [];
						if (preg_match($route->pattern, $subpath, $match) && $this->matchVerb($route, $verb)) {
							array_shift($match);
							$variables = array_merge($variables, $match);

							$subpath = '';
							if ($result = $this->callRoute($route, $verb, $variables)) {
								return $result;
							}
							continue 2;
						}
					}
					// No match - take a break :)
					break;
				}

			}

			// Path is empty, but there may still be ":empty:" paths
			if ($subpath == '' && ($route = $this->subpaths[':empty:'] ?? null) && $this->matchVerb($route, $verb)) {
				if ($result = $this->callRoute($route, $verb, $variables)) {
					return $result;
				}
			}

			// Path is not found
			if ($route = $this->subpaths[':notfound:'] ?? null) {
				if ($result = $this->callRoute($route, $verb, $variables)) {
					return $result;
				}
			}

			// No matches
			throw new \Exception("No route: " . print_r([$verb, $path], true), 404);
		}

		private function matchVerb($route, $verb)
		{
			return $route->verbs === false || in_array($verb, $route->verbs);
		}

		private function callRoute($route, $verb, $variables)
		{
			$this->subpaths = [];
			$this->subpatterns = [];

			// Check filters
			// Filters are run in order, and they don't know anything about each other.
			// The first to return anything other than null aborts the sequence.
			foreach ($route->filters as &$filter) {
				$result = call_user_func_array($filter, array_merge([$this, $verb], $variables));
				if ($result !== null) {
					return $result;
				}
			}

			// Call the route callable to get a result
			return call_user_func_array($route->callback, array_merge([$this], $variables));
		}

		/**
		 * Create path-object used to route.
		 */
		private function createPath($callback, $verbs, $filters, $pattern = null)
		{
			return new class ($callback, $verbs, $filters, $pattern) {
				/** @var callable */
				public $callback;
				/** @var string[] */
				public $verbs;
				/** @var string */
				public $pattern = null;
				/** @var callable[] */
				public $filters;

				public function __construct($callback, $verbs, $filters, $pattern)
				{
					$this->callback = $callback;
					$this->verbs = $verbs;
					$this->filters = $filters;
					$this->pattern = $pattern;
				}
			};
		}

	}

}