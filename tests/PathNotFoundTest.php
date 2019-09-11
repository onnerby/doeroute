<?php
declare(strict_types=1);

namespace Doe\Tests
{

use PHPUnit\Framework\TestCase;

final class PathNotFoundTest extends TestCase
{

	private function getPath()
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
				$router->pathNotFound(function ($router, $var1) {
					return 'notfoundsub' . $var1;
				});
			});
			$router->pathNotFound(function ($router) {
				return 'notfound';
			});
		});
		return $router;
	}

	public function testNotFound(): void
	{
		$router = $this->getPath();
		$this->assertSame(
			$router->route('get', 'testpath/tjobba'), 
			'notfound'
		);
	}

	public function testNotFoundWithVariables(): void
	{
		$router = $this->getPath();
		$this->assertSame(
			$router->route('get', 'testpath/subpath4/fel'), 
			'notfoundsub4'
		);
	}

	public function testNotFoundThatShouldThrow(): void
	{
		$router = $this->getPath();
		$this->expectException(\Exception::class);
		$router->route('get', 'testpath1/subpath4/fel');
	}

}
}