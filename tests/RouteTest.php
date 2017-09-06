<?php
namespace Snout\Tests;

use PHPUnit\Framework\TestCase;
use Ds\Map;
use Snout\Exceptions\RouterException;
use Snout\Exceptions\ConfigurationException;
use Snout\Lexer;
use Snout\Parser;
use Snout\Route;
use Snout\Parameter;

class RouteTest extends TestCase
{
    public function test() : void
    {
        $test_parameters = new Map([
            'id'   => new Parameter('id', 'int', 12),
            'name' => new Parameter('name', 'string', 'luther')
        ]);

        $routed = false;

        $test_controllers = new Map();
        $test_controllers->put(
            'get',
            function (Map $parameters) use ($test_parameters, &$routed) {
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
        );


        $route = new Route([
            'name'        => 'test_route',
            'path'        => '/user/{id: int}/name/{name: string}',
            'controllers' => $test_controllers
        ]);

        $this->assertEquals(
            '/user/{id: int}/name/{name: string}',
            $route->getPath()
        );

        $this->assertEquals('test_route', $route->getName());
        $this->assertTrue($route->hasController('get'));
        $this->assertFalse($route->hasSubRouter());

        $request = new Parser(
            \Snout\array_to_map([
                'invalid' => [
                    'SPACE',
                    'TAB',
                    'NEW_LINE',
                    'CARRIAGE_RETURN'
                ]
            ]),
            new Lexer('/user/12/name/luther')
        );

        while (!$request->isEnd()) {
            $this->assertTrue($route->match($request));
            $request->accept();
        }

        $parameters = $route->getParameters();
        $test_parameters->map(
            function ($name, $parameter) use ($parameters) {
                $this->assertTrue($parameters->hasKey($name));
                $this->assertTrue($parameter->compare($parameters->get($name)));
            }
        );

        $route->runController('get');

        $this->assertTrue($routed);
        $this->assertTrue($route->isComplete());
    }

    // public function testUnmatchingRoute() : void
    // {
    //     $route = new Route([
    //         'name'        => 'test_route',
    //         'path'        => '/user/{id: int}/name/{name: string}',
    //         'controllers' => new Map()
    //     ]);

    //     $request = new Parser(
    //         \Snout\array_to_map([
    //             'invalid' => [
    //                 'SPACE',
    //                 'TAB',
    //                 'NEW_LINE',
    //                 'CARRIAGE_RETURN'
    //             ]
    //         ]),
    //         new Lexer('/foo')
    //     );

    //     $this->assertTrue($route->match($request));
    //     $this->assertFalse($route->match($request));
    //     $this->assertEquals(new Map(), $route->getParameters());
    // }

    // public function testInvalidConfig() : void
    // {
    //     $this->expectException(\InvalidArgumentException::class);
    //     $this->expectExceptionMessage(
    //         '$config must be an array or instance of \Ds\Map.'
    //     );

    //     $route = new Route('foo');
    // }

    // public function testNoControllerOrSubRouter() : void
    // {
    //     $this->expectException(ConfigurationException::class);
    //     $this->expectExceptionMessage(
    //         "Invalid configuration. Require option 'controllers' or 'sub_router'"
    //     );

    //     $route = new Route([
    //         'name'        => 'test_route',
    //         'path'        => '/user/{id: int}/name/{name: string}'
    //     ]);

    //     $route->runController('get');
    // }

    // public function testUnallowedMethod() : void
    // {
    //     $this->expectException(RouterException::class);
    //     $this->expectExceptionMessage("Method 'get' not allowed.");

    //     $route = new Route([
    //         'name'        => 'test_route',
    //         'path'        => '/user/{id: int}/name/{name: string}',
    //         'controllers' => new Map()
    //     ]);

    //     $this->assertFalse($route->hasController('get'));
    //     $route->runController('get');
    // }

    // public function testInvalidParameterType() : void
    // {
    //     $this->expectException(RouterException::class);
    //     $this->expectExceptionMessage("Invalid parameter type 'invalid'.");

    //     $route = new Route([
    //         'name'        => 'test_route',
    //         'path'        => '/invalid/{invalid: invalid}',
    //         'controllers' => new Map()
    //     ]);

    //     $request = new Parser(
    //         \Snout\array_to_map([
    //             'invalid' => [
    //                 'SPACE',
    //                 'TAB',
    //                 'NEW_LINE',
    //                 'CARRIAGE_RETURN'
    //             ]
    //         ]),
    //         new Lexer('/invalid/foo')
    //     );

    //     while (!$request->isEnd()) {
    //         $this->assertTrue($route->match($request));
    //         $request->accept();
    //     }
    // }
}
