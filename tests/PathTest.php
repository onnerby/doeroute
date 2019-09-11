<?php
declare(strict_types=1);

namespace Doe\Tests
{

use PHPUnit\Framework\TestCase;

final class PathTest extends TestCase
{

	public function testCreateRouter(): void
	{
		$router = new \Doe\Router();
		$this->assertIsObject(
			$router
		);

		$router = new \Doe\Router(['get', 'post']);
		$this->assertIsObject(
			$router
		);
	}

	private function getBasicPath()
	{
		$router = new \Doe\Router(['get', 'post']);
		$router->path('testpath', function () {
			return 'testpath';
		});
		return $router;		
	}

	public function testRouterPath(): void
	{
		$router = $this->getBasicPath();
		$this->assertSame(
			$router->route('get', 'testpath'), 
			'testpath'
		);
	}

	public function testRouterInvalidPath(): void
	{
		$router = $this->getBasicPath();
		$this->expectException(\Exception::class);
		$router->route('get', 'invalidpath');
	}

	private function getNestedPath()
	{
		$router = new \Doe\Router(['get', 'post']);
		$router->path('testpath', function ($router) {
			$router->path('subpath1', function ($router) {
				return 'subpath1';
			});
			$router->path('subpath2', function ($router) {
				return 'subpath2';
			});
		});
		return $router;		
	}

	public function testNestedPath(): void
	{
		$router = $this->getNestedPath();
		$this->assertIsObject($router);
		$this->assertSame(
			$router->route('get', 'testpath/subpath1'),
			'subpath1'
		);

		$router = $this->getNestedPath();
		$this->assertSame(
			$router->route('get', 'testpath/subpath2'), 
			'subpath2'
		);

	}

	public function testInvalidNestedPath(): void
	{
		$router = $this->getBasicPath();
		$this->expectException(\Exception::class);
		$router->route('get', 'invalidpath');
		$router->route('get', 'testpath/invalidsubpath');
	}

}
}