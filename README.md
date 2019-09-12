# doeroute
A fast and intuitive PHP Router.
Don't make things more complicated than they needs to be.

## \Doe\Router
The Doe\Router is a router where you build your routes using subpaths and closures. 
The advantage is that the closures are only called if the subpath match which makes it SUPER FAST and easy to follow.
It also makes it very easy to delegate specific paths to some kind of controller/action-pattern.
After I wrote Doe\Router I found [FastRoute](https://github.com/nikic/FastRoute) that is awesome and very similar to this router and probably a bit more felxible when it comes to multiple variables embedded in the path. They are similar in may ways, but the pattern is slightly different with both pros and cons.

## Installation
```
composer require onnerby/doeroute
```

### Basic example

```php
$router = new \Doe\Router(['get', 'post']);
$router->path('blog', function($router) {
	// This closure is called if the route starts with /blog

	$router->path('list', 'get', function ($router) {
		// This is returned when route goes to "/blog/list"
		return "List of all posts";
	});
	$router->path('tags', 'get', function ($router) {
		// This is returned when route goes to "/blog/tags"
		return "List of all tags";
	});
	$router->pathVariable('/^([0-9]+)$/', function ($router, $postId) {
		// This is returned when route goes to something like "/blog/1234"
		return "Post " . $postId;
	});
});

$verb = $_SERVER['REQUEST_METHOD'] == 'GET' ? 'get' : 'post'; // probably more complicated ;)
$path = $_SERVER['DOCUMENT_URI'];
echo $router->route($verb, $path);

```

### Controller example
If you are building bigger webapps you may like to delegate routes to some kind of controller. The Doe\Router is not connected to any kind of pattern for this - but it's still super simple to delegate the route.
```php
// Main app
$router = new \Doe\Router(['get', 'post']);
// Route everything starting with /project to our \Controller_Project::route
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
		
		$router->path('list', 'get', [$controller, 'list'] );

		$router->pathVariable('/^([0-9]+)$/', function ($router, $projectId) use ($controller) {

			// Any generic method needed for everything
			$controller->getProject($projectId);	

			$router->path('overview', 'get', [$controller, 'overview'] );
			$router->path('save', 'post', [$controller, 'save'] );

		});

		// Lets also map the "/project" path to the controllers "list" action
		$router->pathEmpty('get', [$controller, 'list']);

		$router->pathNotFound([$controller, 'error']);

	}

	private function getProject($projectId) { /* Get the project somehow from a database? */ }

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

	public function error($router)
	{
		// Anything not found under "/project/xxxxx"
		return 'You look lost. How can I help?';
	}


}
```

### Filters
You may also use filters to execute stuff before the routes.
```php
// In main app
function authorize($router, $verb) {
	// Authorize user somehow
	if (!($user = getUser())) {
		// Returning anything in "before"-filters will interrupt the route.
		return 'You do not have access to this area.';
	}
}

$router = new \Doe\Router(['get', 'post']);
$router->filter('authorize', function($router) {
	$router->path('restrictedarea', function ($router) {
		return "Warning: Resticted area. Authorized personnel only.";
	});
});

```

# Why another router?
Most routers I've used overcomplicate things.
A router is used on the web for parsing the path of the URL to some kind of action. So lets just do that.

A [URL](https://en.wikipedia.org/wiki/URL) is separated by a `/` and while most routers will define all routes for all paths at once, I decided to parse one segment at a time.
I mean - you probably want to do something on each segment anyway in the end. For instance if I build the following routes:
 - `/user` listing all users
 - `/user/123` showing a users profile page
 - `/user/123/badges` showing a users profile page with the users "badges"
 - `/user/123/projects` showing a users profile page with the users projects

etc, etc.
You will not go directly to define `/user/[0-9]+/project` but will define the different segments down to the whole final path.

The upside with this is that we don't need to define all routes for each request. Instead we look for the first segments and once that's found, we parse the next segment.
There is also the advantage that everything inside `/user/[0-9]+` has its own callable where we can check access to the user before sending the route futher down the next segment.


