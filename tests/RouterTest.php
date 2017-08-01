<?php
namespace Snout\Tests;

use PHPUnit\Framework\TestCase;
use Ds\Map;
use Ds\Deque;
use Snout\Router;

class RouterTest extends TestCase
{
    public function testRouter() : void
    {
        $test_parameters = new Deque([
            new Map([
                'name'  => 'id',
                'type'  => 'int',
                'value' => 21
            ])
        ]);

        $router = new Router();
        $router->route(
            '/user/{id: int}',
            [
                'get' => function(Deque $parameters) use ($test_parameters) {
                    $this->assertEquals($test_parameters, $parameters);
                }
            ]
        );

        $router->route(
            '/foo',
            [
                'get' => function(Deque $parameters) use ($test_parameters) {
                    $this->assertEquals($test_parameters, $parameters);
                }
            ]
        );

        $router->route(
            '/123',
            [
                'get' => function(Deque $parameters) use ($test_parameters) {
                    $this->assertEquals($test_parameters, $parameters);
                }
            ]
        );

        $route = $router->match('/user/21');
        $route->runController('get');
    }
}
