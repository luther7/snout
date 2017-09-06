<?php
namespace Snout\Tests;

use PHPUnit\Framework\TestCase;
use Ds\Map;
use Snout\Exceptions\RouterException;
use Snout\Route;
use Snout\Parameter;
use Snout\Router;
use Snout\Controller;

class RouterTest extends TestCase
{
    public function test() : void
    {
        $test_parameters = new Map(['id' => new Parameter('id', 'int', 21)]);
        $test_controllers = [
            'get' => function (Map $parameters) use ($test_parameters, &$routed) {
                $test_parameters->map(
                    function ($name, $parameter) use ($parameters) {
                        $this->assertTrue($parameters->hasKey($name));
                        $this->assertTrue(
                            $parameter->compare($parameters->get($name))
                        );
                    }
                );
                $routed = true;
            }
        ];

        $routed = false;

        $router = new Router();
        $router->push(new Route([
            'name'        => 'should_run',
            'path'        => '/user/{id: int}',
            'controllers' =>  $test_controllers
        ]));

        $router->push(new Route([
            'name'        => 'should_not_run_1',
            'path'        => '/foo',
            'controllers' => []
        ]));

        $router->push(new Route([
            'name'        => 'should_not_run_2',
            'path'        => '/123',
            'controllers' => []
        ]));

        $router->run('/user/21', 'get');

        $this->assertTrue($routed);
    }

    public function testNoRoutes() : void
    {
        $this->expectException(RouterException::class);
        $this->expectExceptionMessage('No routes were specified.');

        $router = new Router();
        $route = $router->run('/user/21', 'get');
    }

    public function testCustomParameterType() : void
    {
        $test_parameters = new Map([
            'name' => new Parameter('name', 'label', 'foo[]')
        ]);

        $test_controllers = [
            'get' => function (Map $parameters) use ($test_parameters, &$routed) {
                $test_parameters->map(
                    function ($name, $parameter) use ($parameters) {
                        $this->assertTrue($parameters->hasKey($name));
                        $this->assertTrue(
                            $parameter->compare($parameters->get($name))
                        );
                    }
                );
                $routed = true;
            }
        ];

        $routed = false;

        $router = new Router();
        $router->push(new Route([
            'name'        => 'should_run',
            'path'        => '/name/{name: label}',
            'controllers' => $test_controllers,
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

        $route = $router->run('/name/foo[]', 'get');

        $this->assertTrue($routed);
    }

    public function testSubRouting() : void
    {
        $test_parameters = new Map(['id' => new Parameter('id', 'int', 21)]);

        $sub_router = new Router();
        $sub_router->push(new Route([
            'name'        => 'sub_router',
            'path'        => '/{id: int}',
            'controllers' => [
                'get' => function (Map $parameters) use ($test_parameters) {
                    $this->assertEquals($test_parameters, $parameters);
                }
            ]
        ]));

        $router = new Router();
        $router->push(new Route([
            'name'       => 'router',
            'path'       => '/user',
            'sub_router' => $sub_router
        ]));

        $route = $router->run('/user/21', 'get');
    }
}
