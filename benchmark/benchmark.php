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

function getKleinRouter() {

	$router = new \Klein\Klein();

	$router->respond('/profile', function ($rq) { return 'empty'; });
	$router->respond('/profile/overview', function ($rq) { return 'overview'; });
	$router->respond('/profile/stuff', function ($rq) { return 'stuff'; });
	$router->respond('/profile/list', function ($rq) { return 'list'; });
	$router->respond('/profile/save', function ($rq) { return 'save'; });

	$router->respond('/profile/[i:id]', function ($rq) { return 'var'; });
	$router->respond('/profile/[i:id]/[a:var]', function ($rq) { return 'subproject'; });

	$router->respond('/projekt', function ($rq) { return 'empty'; });
	$router->respond('/projekt/overview', function ($rq) { return 'overview'; });
	$router->respond('/projekt/stuff', function ($rq) { return 'stuff'; });
	$router->respond('/projekt/list', function ($rq) { return 'list'; });
	$router->respond('/projekt/save', function ($rq) { return 'save'; });

	$router->respond('/projekt/[a:var]', function ($rq) { return 'var' . $rq->param('var'); });

	$router->respond('/tjosan', function ($rq) { return 'tjosan'; });
	$router->respond('/listings', function ($rq) { return 'listings'; });
	$router->respond('/stuff', function ($rq) { return 'stuff'; });
	$router->respond('/something', function ($rq) { return 'something'; });
	$router->respond('/bok', function ($rq) { return 'bok'; });

	$router->respond('/', function ($rq) { return 'tomt'; });

	return $router;
}

function getAltoRouter() {

	$router = new \AltoRouter();

	$router->map('GET', '/profile', function () { return 'empty'; });
	$router->map('GET', '/profile/overview', function () { return 'overview'; });
	$router->map('GET', '/profile/stuff', function () { return 'stuff'; });
	$router->map('GET', '/profile/list', function () { return 'list'; });
	$router->map('GET', '/profile/save', function () { return 'save'; });

	$router->map('GET', '/profile/[i:id]', function ($id) { return 'var'; });
	$router->map('GET', '/profile/[i:id]/[a:var]', function ($id, $v) { return 'subproject'; });

	$router->map('GET', '/projekt', function () { return 'empty'; });
	$router->map('GET', '/projekt/overview', function () { return 'overview'; });
	$router->map('GET', '/projekt/stuff', function () { return 'stuff'; });
	$router->map('GET', '/projekt/list', function () { return 'list'; });
	$router->map('GET', '/projekt/save', function () { return 'save'; });

	$router->map('GET', '/projekt/[a:var]', function ($v) { return 'var' . $v; });

	$router->map('GET', '/tjosan', function () { return 'tjosan'; });
	$router->map('GET', '/listings', function () { return 'listings'; });
	$router->map('GET', '/stuff', function () { return 'stuff'; });
	$router->map('GET', '/something', function () { return 'something'; });
	$router->map('GET', '/bok', function () { return 'bok'; });

	$router->map('GET', '/', function () { return 'tomt'; });

	return $router;
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

$loops = 1000;

////////////////////////////////////////////////
// Benchmark DoeRouter
$s = microtime(true);
for ($i = 0; $i < $loops; $i++) {
	$router = getDoeRouter();
	$path = $paths[array_rand($paths)];
	$out = $router->route('GET', $path);
	unset($router);
}
$time = microtime(true) - $s;
echo "Doe/Route: " . $time . "s " . round(1000000 * $time / $loops, 2) . "ns/req\n\n";
$doeTime = $time;


////////////////////////////////////////////////
// Benchmark FastRoute
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
echo "Speed improvement: " . round($time / $doeTime, 3) . " times\n\n";


////////////////////////////////////////////////
// Benchmark Klein
$s = microtime(true);
for ($i = 0; $i < $loops; $i++) {
	$router = getKleinRouter();
	$request = new \Klein\Request([], [], [], [
		'REQUEST_METHOD' => 'GET',
		'REQUEST_URI' => $paths[array_rand($paths)],
	]);
	$routerInfo = $router->dispatch($request, null, false);
	unset($router);
}
$time = microtime(true) - $s;
echo "Klein: " . $time . "s " . round(1000000 * $time / $loops, 2) . "ns/req\n";
echo "Speed improvement: " . round($time / $doeTime, 3) . " times\n\n";


////////////////////////////////////////////////
// Benchmark Alto
$s = microtime(true);
for ($i = 0; $i < $loops; $i++) {
	$router = getAltoRouter();
	$routerInfo = $router->match($paths[array_rand($paths)], 'GET');
	if( is_array($routerInfo) && is_callable($routerInfo['target'])) {
		$out = call_user_func_array($routerInfo['target'], $routerInfo['params'] ); 
	}
	unset($router);
}
$time = microtime(true) - $s;
echo "Alto: " . $time . "s " . round(1000000 * $time / $loops, 2) . "ns/req\n";
echo "Speed improvement: " . round($time / $doeTime, 3) . " times\n\n";


