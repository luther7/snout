<?php
namespace Snout\Tests;

use PHPUnit\Framework\TestCase;
use Ds\Map;
use Snout\Exceptions\RouterException;
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

        $test_controllers = new Map();
        $test_controllers->put(
            'get',
            function (Map $parameters) use ($test_parameters) {
                $this->assertEquals($test_parameters, $parameters);
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
        $this->assertEquals($test_controllers, $route->getControllers());

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

        $this->assertEquals($test_parameters, $route->getParameters());
        $route->runController('get');
    }

    public function testUnmatchingRoute() : void
    {
        $route = new Route([
            'name'        => 'test_route',
            'path'        => '/user/{id: int}/name/{name: string}',
            'controllers' => new Map()
        ]);

        $request = new Parser(
            \Snout\array_to_map([
                'invalid' => [
                    'SPACE',
                    'TAB',
                    'NEW_LINE',
                    'CARRIAGE_RETURN'
                ]
            ]),
            new Lexer('/foo')
        );

        $this->assertTrue($route->match($request));
        $this->assertFalse($route->match($request));
        $this->assertEquals(new Map(), $route->getParameters());
    }

    public function testInvalidConfig() : void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            '$config must be an array or instance of \Ds\Map.'
        );

        $route = new Route('foo');
    }

    public function testUnallowedMethod() : void
    {
        $this->expectException(RouterException::class);
        $this->expectExceptionMessage("Method 'get' not allowed.");

        $route = new Route([
            'name'        => 'test_route',
            'path'        => '/user/{id: int}/name/{name: string}',
            'controllers' => new Map()
        ]);

        $route->runController('get');
    }

    public function testInvalidParameterType() : void
    {
        $this->expectException(RouterException::class);
        $this->expectExceptionMessage("Invalid parameter type 'invalid'.");

        $route = new Route([
            'name'        => 'test_route',
            'path'        => '/invalid/{invalid: invalid}',
            'controllers' => new Map()
        ]);

        $request = new Parser(
            \Snout\array_to_map([
                'invalid' => [
                    'SPACE',
                    'TAB',
                    'NEW_LINE',
                    'CARRIAGE_RETURN'
                ]
            ]),
            new Lexer('/invalid/foo')
        );

        while (!$request->isEnd()) {
            $this->assertTrue($route->match($request));
            $request->accept();
        }
    }
}
