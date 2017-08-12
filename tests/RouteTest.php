<?php
namespace Snout\Tests;

use PHPUnit\Framework\TestCase;
use Ds\Map;
use Snout\Lexer;
use Snout\Parser;
use Snout\Route;
use Snout\Parameter;

class RouteTest extends TestCase
{
    public function testMatchingRoute() : void
    {
        $test_parameters = new Map([
            'id'   => new Parameter('id', 'int', 12),
            'name' => new Parameter('name', 'string', 'luther')
        ]);

        $route = new Route([
            'name'        => 'test_route',
            'path'        => '/user/{id: int}/name/{name: string}',
            'controllers' => [
                'get' => function (Map $parameters) use ($test_parameters) {
                    $this->assertEquals($test_parameters, $parameters);
                }
            ]
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
        $test_parameters = new Map([
            'id'   => new Parameter('id', 'int', 12),
            'name' => new Parameter('name', 'string', 'luther')
        ]);

        $route = new Route([
            'name'        => 'test_route',
            'path'        => '/user/{id: int}/name/{name: string}',
            'controllers' => [
                'get' => function (Map $parameters) use ($test_parameters) {
                    $this->assertEquals($test_parameters, $parameters);
                }
            ]
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
}
