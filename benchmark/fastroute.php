<?php
require __DIR__ . '/../vendor/autoload.php';

/***************************************************

In this benchmark we try to build a realistic
route for a pretty "normal" project.
The benchmark tries to replicate a standard request
by creating the routes for each loop and make one 
random call to any of the routes each time.

***************************************************/
 

function getDoeRouter() {

	$router = new \Doe\Router(['GET', 'POST']);

	$router->path('profile', function ($router) {
		$router->path('overview', 'GET', function ($router) { return 'overview'; });
		$router->path('stuff', 'GET', function ($router) { return 'stuff'; });
		$router->path('list', 'GET', function ($router) { return 'list'; });
		$router->path('save', 'GET', function ($router) { return 'korv'; });
		$router->pathEmpty(function ($router) { return 'empty'; });
		$router->pathVariable('/^([0-9]+)$/', function ($router, $var1) {
			$router->pathVariable('/^([a-z]+)$/', function ($router, $var1, $var2) {
				return 'subproject';
			});
			$router->pathEmpty(function ($router) { return 'var'; });
		});
	});

	$router->path('project', function ($router) {
		$router->path('overview', 'GET', function ($router) { return 'overview'; });
		$router->path('stuff', 'GET', function ($router) { return 'stuff'; });
		$router->path('list', 'GET', function ($router) { return 'list'; });
		$router->path('save', 'GET', function ($router) { return 'korv'; });
		$router->pathEmpty(function ($router) { return 'empty'; });
		$router->pathVariable('/^([0-9]+)$/', function ($router, $var1) {
			return 'var' . $var1;
		});
	});
	$router->path('tjosan', 'GET', function ($router) { return 'tjosan'; });
	$router->path('listings', 'GET', function ($router) { return 'listings'; });
	$router->path('stuff', 'GET', function ($router) { return 'stuff'; });
	$router->path('something', 'GET', function ($router) { return 'something'; });
	$router->path('bok', 'GET', function ($router) { return 'bok'; });
	$router->pathEmpty(function ($router) { return 'tomt'; });

	return $router;
}

function getFastRouter() {
	$dispatcher = FastRoute\simpleDispatcher(function(FastRoute\RouteCollector $router) {
		$router->addRoute('GET', '/profile', function () { return 'empty'; });
		$router->addGroup('/profile', function ($router) {
			$router->addRoute('GET', '/overview', function () { return 'overview'; });
			$router->addRoute('GET', '/stuff', function () { return 'stuff'; });
			$router->addRoute('GET', '/list', function () { return 'list'; });
			$router->addRoute('GET', '/save', function () { return 'save'; });
			$router->addRoute('GET', '/{id:[0-9]+}', function () { return 'var'; });
			$router->addRoute('GET', '/{id:[0-9]+}/{sub:[a-z]+}', function () { return 'subproject'; });
		});
		$router->addRoute('GET', '/project', function () { return 'empty'; });
		$router->addGroup('/project', function ($router) {
			$router->addRoute('GET', '/overview', function () { return 'overview'; });
			$router->addRoute('GET', '/stuff', function () { return 'stuff'; });
			$router->addRoute('GET', '/list', function () { return 'list'; });
			$router->addRoute('GET', '/save', function () { return 'save'; });
			$router->addRoute('GET', '/{id:[0-9]+}', function () { return 'var'; });
		});
		$router->addRoute('GET', '/tjosan', function () { return 'tjosan'; });
		$router->addRoute('GET', '/listings', function () { return 'listings'; });
		$router->addRoute('GET', '/stuff', function () { return 'stuff'; });
		$router->addRoute('GET', '/something', function () { return 'something'; });
		$router->addRoute('GET', '/bok', function () { return 'bok'; });
	});
	return $dispatcher;
}


$paths = [
	'/profile',
	'/profile/overview',
	'/profile/stuff',
	'/profile/list',
	'/profile/123',
	'/profile/12',
	'/profile/523',
	'/profile/723',
	'/profile/123',
	'/profile/234/tksgw',
	'/profile/5678/wwoqmva',
	'/profile/12463/nkflewnfew',
	'/profile/4232/fwfehjreklfhewakfwa',
	'/profile/129455928239/dkd',
	'/project',
	'/project/overview',
	'/project/stuff',
	'/project/list',
	'/project/123',
	'/project/12',
	'/project/523',
	'/project/723',
	'/project/123',
	'/tjosan',
	'/listings',
	'/stuff',
	'/something',
	'/bok',
];

$loops = 10000;
////////////////////////////////////////////////
$s = microtime(true);
for ($i = 0; $i < $loops; $i++) {
	$router = getDoeRouter();
	$path = $paths[array_rand($paths)];
	$out = $router->route('GET', $path);
	unset($router);
}
$time = microtime(true) - $s;
echo "Doe/Route: " . $time . "s " . round(1000000 * $time / $loops, 2) . "ns/req\n";
$doeTime = $time;

////////////////////////////////////////////////
$s = microtime(true);
for ($i = 0; $i < $loops; $i++) {
	$router = getFastRouter();
	$path = $paths[array_rand($paths)];
	$routerInfo = $router->dispatch('GET', $path);
	$out = $routerInfo[1]($routerInfo[2]);
	unset($router);
}
$time = microtime(true) - $s;
echo "FastRoute: " . $time . "s " . round(1000000 * $time / $loops, 2) . "ns/req\n";


echo "Speed improvement: " . round($time / $doeTime, 3) . " times\n";

