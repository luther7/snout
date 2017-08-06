<?php
namespace Snout\Tests;

use PHPUnit\Framework\TestCase;
use Ds\Map;
use Ds\Deque;
use Snout\Route;
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
        $router->push(new Route([
            'name'        => 'should_match',
            'path'        => '/user/{id: int}',
            'controllers' => [
                'get' => function(Deque $parameters) use ($test_parameters) {
                    $this->assertEquals($test_parameters, $parameters);
                }
            ]
        ]));

        $router->push(new Route([
            'name'        => 'should_not_match_1',
            'path'        => '/foo',
            'controllers' => [
                'get' => function(Deque $parameters) use ($test_parameters) {
                    $this->assertEquals($test_parameters, $parameters);
                }
            ]
        ]));

        $router->push(new Route([
            'name'        => 'should_not_match_2',
            'path'        => '/123',
            'controllers' => [
                'get' => function(Deque $parameters) use ($test_parameters) {
                    $this->assertEquals($test_parameters, $parameters);
                }
            ]
        ]));

        $route = $router->match('/user/21');
        $route->runController('get');
    }
}
