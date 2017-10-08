<?php
namespace Snout\Tests;

use PHPUnit\Framework\TestCase;
use Ds\Map;
use Snout\Exceptions\RouterException;
use Snout\Parameter;
use Snout\Route;
use Snout\Request;
use Snout\Router;

class RouterTest extends TestCase
{
    public function testMatch() : void
    {
        $routed = false;
        $test_parameters = new Map([
            'id'   => new Parameter('id', 'int', 21),
            'name' => new Parameter('name', 'string', 'foo')
        ]);

        $get = function ($result_parameters) use ($test_parameters, &$routed) {
            $routed = true;

            $test_parameters->map(
                function ($name, $parameter) use ($result_parameters) {
                    $this->assertTrue(
                        $result_parameters->hasKey($name)
                    );
                    $this->assertTrue(
                        $parameter->compare($result_parameters->get($name))
                    );
                }
            );
        };

        $router = new Router();
        $router->push(new Route([
            'name'        => 'should_run',
            'path'        => '/user/{id: int}/name/{name: string}',
            'controllers' => new Map(['get' => $get])
        ]));

        $router->push(new Route([
            'name'        => 'should_not_run_1',
            'path'        => '/foo',
            'controllers' => new Map()
        ]));

        $router->push(new Route([
            'name'        => 'should_not_run_2',
            'path'        => '/bar',
            'controllers' => new Map()
        ]));

        $request = new Request('/user/21/name/foo', 'get');
        $this->assertEquals('get', $request->getMethod());
        $route = $router->match($request);
        $this->assertTrue($route->hasController($request->getMethod()));
        $this->assertFalse($route->hasSubRouter());
        $controller = $route->getController($request->getMethod());
        $parameters = $route->getParameters();
        $controller($route->getParameters());
        $this->assertTrue($routed);
    }

    public function testManualSubRouting() : void
    {
        $sub_routed = false;
        $test_parameters = new Map([
            'id'   => new Parameter('id', 'int', 21)
        ]);

        $get = function ($result_parameters)
 use ($test_parameters, &$sub_routed) {
            $sub_routed = true;

            $test_parameters->map(
                function ($name, $parameter) use ($result_parameters) {
                    $this->assertTrue(
                        $result_parameters->hasKey($name)
                    );
                    $this->assertTrue(
                        $parameter->compare($result_parameters->get($name))
                    );
                }
            );
        };

        $sub_router = new Router();
        $sub_router->push(new Route([
            'name'        => 'sub_router',
            'path'        => '/{id: int}',
            'controllers' => new Map(['get' => $get]),
        ]));

        $routed = false;
        $router = new Router();
        $router->push(new Route([
            'name'       => 'router',
            'path'       => '/user',
            'controllers' => new Map([
                'get' => function () use (&$routed) {
                    $routed = true;
                }
            ]),
            'sub_router' => $sub_router
        ]));

        $request = new Request('/user/21', 'get');
        $this->assertEquals('get', $request->getMethod());
        $route = $router->match($request);
        $this->assertTrue($route->hasController($request->getMethod()));
        $controller = $route->getController($request->getMethod());
        $parameters = $route->getParameters();
        $controller($route->getParameters());

        $this->assertTrue($route->hasSubRouter());
        $sub_router = $route->getSubRouter();
        $sub_route = $sub_router->match($request);
        $this->assertTrue($sub_route->hasController($request->getMethod()));
        $sub_controller = $sub_route->getController($request->getMethod());
        $parameters->putAll($sub_route->getParameters());
        $sub_controller($parameters);

        $this->assertTrue($sub_routed);
        $this->assertTrue($routed);
    }

    public function testRun() : void
    {
        $routed = false;
        $test_parameters = new Map([
            'id'   => new Parameter('id', 'int', 21),
            'name' => new Parameter('name', 'string', 'foo')
        ]);

        $get = function ($result_parameters, $arg) use ($test_parameters, &$routed) {
            $routed = true;
            $this->assertEquals('bar', $arg);

            $test_parameters->map(
                function ($name, $parameter) use ($result_parameters) {
                    $this->assertTrue(
                        $result_parameters->hasKey($name)
                    );
                    $this->assertTrue(
                        $parameter->compare($result_parameters->get($name))
                    );
                }
            );
        };

        $router = new Router();
        $router->push(new Route([
            'name'        => 'should_run',
            'path'        => '/user/{id: int}/name/{name: string}',
            'controllers' => new Map(['get' => $get])
        ]));

        $router->push(new Route([
            'name'        => 'should_not_run_1',
            'path'        => '/foo',
            'controllers' => new Map()
        ]));

        $router->push(new Route([
            'name'        => 'should_not_run_2',
            'path'        => '/bar',
            'controllers' => new Map()
        ]));

        $router->run(new Request('/user/21/name/foo', 'get'), 'bar');
        $this->assertTrue($routed);
    }

    public function testAutomaticSubRouting() : void
    {
        $sub_routed = false;
        $test_parameters = new Map([
            'id'   => new Parameter('id', 'int', 21)
        ]);

        $get = function ($result_parameters, $arg) use ($test_parameters, &$sub_routed) {
            $sub_routed = true;
            $this->assertEquals('bar', $arg);

            $test_parameters->map(
                function ($name, $parameter) use ($result_parameters) {
                    $this->assertTrue(
                        $result_parameters->hasKey($name)
                    );
                    $this->assertTrue(
                        $parameter->compare($result_parameters->get($name))
                    );
                }
            );
        };

        $sub_router = new Router();
        $sub_router->push(new Route([
            'name'        => 'sub_router',
            'path'        => '/{id: int}',
            'controllers' => new Map(['get' => $get]),
        ]));

        $routed = false;
        $router = new Router();
        $router->push(new Route([
            'name'       => 'router',
            'path'       => '/user',
            'controllers' => new Map([
                'get' => function ($parameters, $arg) use (&$routed) {
                    $routed = true;
                    $this->assertTrue($parameters->isEmpty());
                    $this->assertEquals('bar', $arg);
                }
            ]),
            'sub_router' => $sub_router
        ]));

        $router->run(new Request('/user/21', 'get'), $arg = 'bar');
        $this->assertTrue($sub_routed);
        $this->assertTrue($routed);
    }

    public function testCustomParameterType() : void
    {
        $routed = false;
        $test_parameters = new Map([
            'name' => new Parameter('name', 'label', 'foo[]')
        ]);

        $get = function ($result_parameters) use ($test_parameters, &$routed) {
            $routed = true;

            $test_parameters->map(
                function ($name, $parameter) use ($result_parameters) {
                    $this->assertTrue(
                        $result_parameters->hasKey($name)
                    );
                    $this->assertTrue(
                        $parameter->compare($result_parameters->get($name))
                    );
                }
            );
        };

        $router = new Router();
        $router->push(new Route([
            'name'        => 'should_run',
            'path'        => '/name/{name: label}',
            'controllers' => new Map(['get' => $get]),
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

        $request = new Request('/name/foo[]', 'get');
        $this->assertEquals('get', $request->getMethod());
        $route = $router->match($request);
        $this->assertTrue($route->hasController($request->getMethod()));
        $this->assertFalse($route->hasSubRouter());
        $controller = $route->getController($request->getMethod());
        $parameters = $route->getParameters();
        $controller($route->getParameters());
        $this->assertTrue($routed);
    }

    public function testNoRoutes() : void
    {
        $this->expectException(RouterException::class);
        $this->expectExceptionMessage('No routes were specified.');

        $request = new Request('/user/21', 'get');
        $router = new Router();
        $route = $router->match($request);
    }
}
