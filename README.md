# doeroute
A fast and intuitive PHP Router.
Don't make things more complicated than they needs to be.

## \Doe\Router
The Doe\Router is a router where you build your routes using subpaths and closures. 
The advantage is that the closures are only called if the subpath match which makes it SUPER FAST and easy to follow.
It also makes it very easy to delegate specific paths to some kind of controller/action-pattern

## Installation
```
composer require onnerby/doeroute
```

### Basic example

```php
$router = new \Doe\Router(['create', 'read', 'update', 'delete']);
$router->path('blog', function($router) {
	$router->path('list', 'read', function ($router) {
		return "List of all posts";
	});
	$router->path('tags', 'read', function ($router) {
		return "List of all tags";
	});
	$router->pathVariable('/^([0-9]+)$/', function ($router, $postId) {
		return "Post " . $postId;
	});
});

$verb = $_SERVER['REQUEST_METHOD'] == 'GET' ? 'read' : 'update'; // probably more complicated ;)
$path = $_SERVER['DOCUMENT_URI'];
echo $router->route($verb, $path);

```

### Controller example
If you are building bigger webapps you may like to delegate routes to some kind of controller. The Doe\Router is not connected to any kind of pattern for this - but it's still super simple to delegate the route.
```php
// Main app
$router = new \Doe\Router(['create', 'read', 'update', 'delete']);
$router->path('project', ['Controller_Project', 'route']);

...
```
Controller:
```php
class Controller_Project
{
	public static function route($router)
	{
		$controller = new self;
		
		$router->path('list', 'read', [$controller, 'list'] );

		$router->pathVariable('/^([0-9]+)$/', function ($router, $projectId) use ($controller) {

			// Any generic method needed for everything
			$controller->getProject($projectId);	

			$router->path('overview', 'read', [$controller, 'overview'] );
			$router->path('save', 'update', [$controller, 'save'] );

		});

	}

	private function getProject($projectId) { /* Get the project somehow */ }

	public function list($router)
	{
		// Full path to this route is "/project/list"
		return 'List projects';
	}

	public function overview($router, $projectId)
	{
		// Full path to this route is "/project/1234/overview"
		return 'Project ' . $projectId . ' overview';
	}

	public function save($router, $projectId)
	{
		// Full path to this route is "/project/1234/save"
		return 'Saved project ' . $projectId;
	}

}
```

### Filters
You may also use filters to execute stuff before or after the routes.
```php
// In main app
function authorize($info) {
	// Authorize user somehow
	if (!($user = getUser())) {
		// Returning anything in "before"-filters will interrupt the route.
		return 'You do not have access to this area.';
	}
}

$router = new \Doe\Router(['create', 'read', 'update', 'delete']);
$router->filter(['authorize'], null, function($router) {
	$router->path('restrictedarea', function ($router) {
		return "Warning: Resticted area. Authorized personnel only.";
	});
});

```