<?php
namespace Snout\Tests;

use \PHPUnit\Framework\TestCase;
use Ds\Map;
use Ds\Deque;
use Snout\Lexer;
use Snout\Parser;
use Snout\Route;

class RouteTest extends TestCase
{
    public function testMatchingRoute() : void
    {
        $test_parameters = new Deque([
            new Map([
                'name'  => 'id',
                'type'  => 'int',
                'value' => 12
            ]),
            new Map([
                'name'  => 'name',
                'type'  => 'string',
                'value' => 'luther'
            ])
        ]);

        $route = new Route(
            \Snout\array_to_map([
                'parameters' => [
                    'string' => [
                        'DIGIT',
                        'ALPHA',
                        'UNDERSCORE',
                        'HYPHEN',
                        'PERIOD'
                    ],
                    'int' => [
                        'DIGIT'
                    ]
                ]
            ]),
            new Parser(
                \Snout\array_to_map([
                    'invalid' => [
                        'TAB',
                        'NEW_LINE',
                        'CARRIAGE_RETURN'
                    ]
                ]),
                new Lexer('/user/{id: int}/name/{name: string}')
            ),
            \Snout\array_to_map([
                'get' => function(Deque $parameters) use ($test_parameters) {
                    $this->assertEquals($test_parameters, $parameters);
                }
            ])
        );

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
        $route = new Route(
            \Snout\array_to_map([
                'parameters' => [
                    'string' => [
                        'DIGIT',
                        'ALPHA',
                        'UNDERSCORE',
                        'HYPHEN',
                        'PERIOD'
                    ],
                    'int' => [
                        'DIGIT'
                    ]
                ]
            ]),
            new Parser(
                \Snout\array_to_map([
                    'invalid' => [
                        'TAB',
                        'NEW_LINE',
                        'CARRIAGE_RETURN'
                    ]
                ]),
                new Lexer('/user/{id: int}/name/{name: string}')
            ),
            \Snout\array_to_map([
                'get' => function(Deque $parameters) {
                    $this->assertEquals(new Deque(), $parameters);
                }
            ])
        );

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
        $this->assertEquals(new Deque(), $route->getParameters());
    }
}
