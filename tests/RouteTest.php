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
    public function testRoute() : void
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
            \Snout\array_to_map([])
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

        $parameters = new Deque([
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

        while (!$request->isEnd()) {
            $this->assertTrue($route->match($request));
            $request->accept();
        }

        $this->assertEquals($parameters, $route->getParameters());
    }
}
