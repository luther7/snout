<?php
namespace Snout\Tests;

use PHPUnit\Framework\TestCase;
use Ds\Map;
use Snout\Exceptions\RouterException;
use Snout\Parameter;
use Snout\Route;
use Snout\Router;

class RouterTest extends TestCase
{
    public function test() : void
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

        [$controller, $parameters] = $router->match('/user/21/name/foo', 'get');
        $controller($parameters);

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

        [$controller, $parameters] = $router->match('/name/foo[]', 'get');
        $controller($parameters);

        $this->assertTrue($routed);
    }

    public function testSubRouting() : void
    {
        $routed = false;
        $test_parameters = new Map([
            'id'   => new Parameter('id', 'int', 21)
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

        $sub_router = new Router();
        $sub_router->push(new Route([
            'name'        => 'sub_router',
            'path'        => '/{id: int}',
            'controllers' => new Map(['get' => $get]),
        ]));

        $router = new Router();
        $router->push(new Route([
            'name'       => 'router',
            'path'       => '/user',
            'sub_router' => $sub_router
        ]));

        [$controller, $parameters] = $router->match('/user/21', 'get');
        $controller($parameters);

        $this->assertTrue($routed);
    }

    public function testNoRoutes() : void
    {
        $this->expectException(RouterException::class);
        $this->expectExceptionMessage('No routes were specified.');

        $router = new Router();
        $route = $router->match('/foo', 'get');
    }
}
