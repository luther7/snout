<?php
namespace Snout\Tests;

use PHPUnit\Framework\TestCase;
use Ds\Map;
use Snout\Exceptions\RouterException;
use Snout\Route;
use Snout\Parameter;
use Snout\Router;

class RouterTest extends TestCase
{
    public function test() : void
    {
        $test_parameters = new Map(['id' => new Parameter('id', 'int', 21)]);

        $router = new Router();
        $router->push(new Route([
            'name'        => 'should_match',
            'path'        => '/user/{id: int}',
            'controllers' => [
                'get' => function (Map $parameters) use ($test_parameters) {
                    $this->assertEquals($test_parameters, $parameters);
                }
            ]
        ]));

        $router->push(new Route([
            'name'        => 'should_not_match_1',
            'path'        => '/foo',
            'controllers' => [
                'get' => function (Map $parameters) use ($test_parameters) {
                    $this->assertEquals($test_parameters, $parameters);
                }
            ]
        ]));

        $router->push(new Route([
            'name'        => 'should_not_match_2',
            'path'        => '/123',
            'controllers' => [
                'get' => function (Map $parameters) use ($test_parameters) {
                    $this->assertEquals($test_parameters, $parameters);
                }
            ]
        ]));

        $route = $router->match('/user/21');
        $route->runController('get');
    }

    public function testNoRoutes() : void
    {
        $this->expectException(RouterException::class);
        $this->expectExceptionMessage('No routes were specified.');

        $router = new Router();
        $route = $router->match('/user/21');
    }

    public function testCustomParameterType() : void
    {
        $test_parameters = new Map([
            'name' => new Parameter('name', 'label', 'foo[]')
        ]);

        $router = new Router();
        $router->push(new Route([
            'name'        => 'should_match',
            'path'        => '/name/{name: label}',
            'controllers' => [
                'get' => function (Map $parameters) use ($test_parameters) {
                    $this->assertEquals($test_parameters, $parameters);
                }
            ],
            'parameters' => [
                'label' => [
                    'DIGIT',
                    'ALPHA',
                    'UNDERSCORE',
                    'OPEN_BRACKET',
                    'CLOSE_BRACKET'
                ]
            ]
        ]));

        $route = $router->match('/name/foo[]');
        $route->runController('get');
    }
}
