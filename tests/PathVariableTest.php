<?php
declare(strict_types=1);

namespace Doe\Tests
{

use PHPUnit\Framework\TestCase;

final class PathVariableTest extends TestCase
{

	private function getPath()
	{
		$router = new \Doe\Router(['get', 'post']);
		$router->path('testpath', function ($router) {
			$router->pathVariable('/^subpath([0-9]+)$/', function ($router, $var1) {
				return 'varpath' . $var1;
			});
		});
		return $router;		
	}

	public function testVariableRouterPath(): void
	{
		for ($i = 0; $i < 10; $i++) {
			$router = $this->getPath();
			$r = rand(1, 10000);
			$this->assertSame(
				$router->route('get', 'testpath/subpath' . $r), 
				'varpath' . $r
			);
		}
	}

	private function getPathAdv()
	{
		$router = new \Doe\Router(['get', 'post']);
		$router->path('testpath', function ($router) {
			$router->path('tjo', function ($router) {
				return 'tjo';
			});
			$router->pathVariable('/^subpath([0-9]+)$/', function ($router, $var1) {
				$router->pathVariable('/^secondpath([0-9]+)$/', function ($router, $var1, $var2) {
					return 'secondpath' . $var1 . "x" . $var2;
				});
			});
		});
		return $router;		
	}

	public function testNestedVariablePaths(): void
	{
		for ($i = 0; $i < 10; $i++) {
			$router = $this->getPathAdv();
			$r1 = rand(1, 10000);
			$r2 = rand(1, 10000);
			$this->assertSame(
				$router->route('get', 'testpath/subpath' . $r1 . '/secondpath' . $r2), 
				'secondpath' . $r1 . 'x' . $r2
			);
		}
	}

	public function testFailingVariablePaths(): void
	{
		$router = $this->getPathAdv();
		$this->expectException(\Exception::class);
		$router->route('get', 'testpath/subpath1');
	}

	private function getEmptyPathAdv()
	{
		$router = new \Doe\Router(['get', 'post']);
		$router->path('testpath', function ($router) {
			$router->path('tjo', function ($router) {
				return 'tjo';
			});
			$router->pathVariable('/^subpath([0-9]+)$/', function ($router, $var1) {
				$router->pathVariable('/^secondpath([0-9]+)$/', function ($router, $var1, $var2) {
					return 'secondpath' . $var1 . "x" . $var2;
				});
				$router->pathEmpty(function ($router, $var1) {
					return 'emptypath' . $var1;
				});
			});
		});
		return $router;		
	}

	public function testNestedVariableWithEmptyPaths(): void
	{
		for ($i = 0; $i < 10; $i++) {
			$router = $this->getEmptyPathAdv();
			$r1 = rand(1, 10000);
			$this->assertSame(
				$router->route('get', 'testpath/subpath' . $r1), 
				'emptypath' . $r1
			);
		}
	}

}
}