<?php
namespace Snout\Tests;

use PHPUnit\Framework\TestCase;
use InvalidArgumentException;
use Ds\Map;
use Snout\Exceptions\RouterException;
use Snout\Exceptions\ConfigurationException;
use Snout\Lexer;
use Snout\Parser;
use Snout\Route;
use Snout\Parameter;

class RouteTest extends TestCase
{
    public static function configProvider() : array
    {
        $config = \Snout\array_to_map([
            'invalid' => [
                'SPACE',
                'TAB',
                'NEW_LINE',
                'CARRIAGE_RETURN'
            ]
        ]);

        return [[$config]];
    }

    /**
     * @dataProvider configProvider
     */
    public function testRoute(Map $config) : void
    {
        $routed = false;

        $test_parameters = new Map([
            'id'     => new Parameter('id', 'integer', 12),
            'name'   => new Parameter('name', 'string', 'luther'),
            'new'    => new Parameter('new', 'boolean', true),
            'amount' => new Parameter('amount', 'float', 1.23)
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

        $path = '/user/{id: integer}'
              . '/name/{name: string}'
              . '/new/{new: boolean}'
              . '/amount/{amount: float}';

        $route = new Route([
            'name'        => 'test_route',
            'path'        => $path,
            'controllers' => new Map(['get' => $get])
        ]);

        $this->assertEquals('test_route', $route->getName());
        $this->assertEquals($path, $route->getPath());
        $this->assertTrue($route->hasController('get'));
        $this->assertFalse($route->hasController('post'));
        $this->assertFalse($route->hasSubRouter());

        $request = new Parser(
            $config,
            new Lexer('/user/12/name/luther/new/true/amount/1.23')
        );

        while (!$request->isComplete()) {
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

        $controller = $route->getController('get');
        $controller($parameters);

        $this->assertTrue($routed);
        $this->assertTrue($route->isComplete());
    }

    /**
     * @dataProvider configProvider
     */
    public function testUnmatchingRoute(Map $config) : void
    {
        $route = new Route([
            'name'        => 'test_route',
            'path'        => '/user/{id: integer}/name/{name: string}',
            'controllers' => new Map()
        ]);

        $request = new Parser($config, new Lexer('/foo'));

        $this->assertTrue($route->match($request));
        $this->assertFalse($route->match($request));
        $this->assertEquals(new Map(), $route->getParameters());
    }

    /**
     * @dataProvider configProvider
     */
    public function testDebug(Map $config) : void
    {
        $route = new Route([
            'name'        => 'test_route',
            'path'        => '/user/{id: integer}/name/{name: string}',
            'controllers' => new Map(['get' => ''])
        ]);

        $request = new Parser(
            $config,
            new Lexer('/user/12/name/luther')
        );

        while (!$request->isComplete()) {
            $this->assertTrue($route->match($request));
            $request->accept();
        }

        $this->assertEquals(
            '|/|user|/|{|id|:| |integer|}|/|name|/|{|name|:| |string|}|| 19',
            $route->debug()
        );
    }

    public function testInvalidConfigType() : void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            '$config must be an array or an instance of \Ds\Map.'
        );

        $route = new Route('foo');
    }

    public function testInvalidConfig() : void
    {
        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage(
            "Invalid configuration. Missing keys: 'path'."
        );

        $route = new Route([]);
    }

    public function testNoControllerOrSubRouter() : void
    {
        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage(
            "Invalid configuration. Require option 'controllers' or 'sub_router'."
        );

        $route = new Route(['path' => 'foo']);
    }

    public function testNoControllerForMethod() : void
    {
        $this->expectException(RouterException::class);
        $this->expectExceptionMessage("No controller for method 'get'.");

        $route = new Route([
            'path'        => 'foo',
            'controllers' => new Map()
        ]);

        $this->assertFalse($route->hasController('get'));
        $controller = $route->getController('get');
    }

    public function testSubRouterNotFound() : void
    {
        $this->expectException(RouterException::class);
        $this->expectExceptionMessage("Sub-router not found.");

        $route = new Route([
            'path'        => 'foo',
            'controllers' => new Map()
        ]);

        $controller = $route->getSubRouter();
    }

    /**
     * @dataProvider configProvider
     */
    public function testInvalidEmbeddedParameterType(Map $config) : void
    {
        $this->expectException(RouterException::class);
        $this->expectExceptionMessage(
            "Invalid embedded parameter type 'invalid'. "
            . "In route /invalid/{invalid: invalid}."
        );

        $route = new Route([
            'path'        => '/invalid/{invalid: invalid}',
            'controllers' => new Map()
        ]);

        $request = new Parser($config, new Lexer('/invalid/invalid'));

        while (!$request->isComplete()) {
            $this->assertTrue($route->match($request));
            $request->accept();
        }
    }

    /**
     * @dataProvider configProvider
     */
    public function testDuplicateParameterName(Map $config) : void
    {
        $this->expectException(RouterException::class);
        $this->expectExceptionMessage(
            "Duplicate embedded parameter name 'duplicate'. "
            . "In route /{duplicate: integer}/{duplicate: integer}."
        );

        $route = new Route([
            'path'        => '/{duplicate: integer}/{duplicate: integer}',
            'controllers' => new Map()
        ]);

        $request = new Parser($config, new Lexer('/12/34'));

        while (!$request->isComplete()) {
            $this->assertTrue($route->match($request));
            $request->accept();
        }
    }
}
