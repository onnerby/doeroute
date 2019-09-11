<?php
declare(strict_types=1);

namespace Doe\Tests
{

use PHPUnit\Framework\TestCase;

final class FilterTest extends TestCase
{

	public function testPathOutsideFilter(): void
	{
		$filterCounter = 0;
		$router = new \Doe\Router(['get', 'post']);
		$router->path('testpath', function ($router) { return 'testpath'; });
		$router->filter( function () use (&$filterCounter) {
			$filterCounter ++;
		}, function ($router) {
			$router->path('testpathwithfilter', function ($router) { return 'testpathwithfilter'; });
		});

		$this->assertSame($router->route('get', 'testpath'), 'testpath');
		$this->assertSame($filterCounter, 0);
	}

	public function testPathInsideFilter(): void
	{
		$filterCounter = 0;
		$router = new \Doe\Router(['get', 'post']);
		$router->path('testpath', function ($router) { return 'testpath'; });
		$router->filter( function () use (&$filterCounter) {
			$filterCounter ++;
		}, function ($router) {
			$router->path('testpathwithfilter', function ($router) { return 'testpathwithfilter'; });
		});

		$this->assertSame($router->route('get', 'testpathwithfilter'), 'testpathwithfilter');
		$this->assertSame($filterCounter, 1);
	}

	public function testFilterInSubpath(): void
	{
		$filterCounter = 0;
		$router = new \Doe\Router(['get', 'post']);
		$router->path('testpath', function ($router) { return 'testpath'; });
		$router->filter( function () use (&$filterCounter) {
			$filterCounter ++;
		}, function ($router) {
			$router->path('testpathwithfilter', function ($router) {
				$router->path('subpath', function ($router) {
					return 'subpath';
				});
			});
		});

		$this->assertSame($router->route('get', 'testpathwithfilter/subpath'), 'subpath');
		$this->assertSame($filterCounter, 1);
	}

	public function testFilterInterupt(): void
	{
		$router = new \Doe\Router(['get', 'post']);
		$router->path('testpath', function ($router) { return 'testpath'; });
		$router->filter(function ($router, $verb) {
			return 'filtercalled';
		}, function ($router) {
			$router->path('testpathwithfilter', function ($router) {
				$router->pathVariable('/^([0-9]+)$/', function ($router, $var1) {
					return 'subpath';
				});
			});
		});

		$this->assertSame($router->route('get', 'testpathwithfilter/subpath'), 'filtercalled');

	}

	public function testFilterVariables(): void
	{
		$router = new \Doe\Router(['get', 'post']);
		$router->path('testpathwithfilter', function ($router) {
			$router->pathVariable('/^([0-9]+)$/', function ($router, $var1) {
				$router->filter(function ($router, $verb, $var1) {
					return 'filtercalled' . $var1;
				}, function ($router) {
					$router->pathEmpty(function ($router) {
						return 'subpath';
					});
				});
			});
		});

		$this->assertSame($router->route('get', 'testpathwithfilter/3'), 'filtercalled3');

	}

}
}