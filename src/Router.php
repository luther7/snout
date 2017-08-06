<?php
namespace Snout;

use Ds\Map;
use Ds\Deque;
use Snout\Exceptions\RouterException;
use Snout\Lexer;
use Snout\Parser;
use Snout\Route;

/**
 * Router.
 */
class Router
{
    /**
     * @const array DEFAULT_CONFIG
     */
    private const DEFAULT_CONFIG = [
        'request' => [
            'parser' => [
                'invalid' => [
                    'SPACE',
                    'TAB',
                    'NEW_LINE',
                    'CARRIAGE_RETURN'
                ]
            ]
        ]
    ];

    /**
     * @var Map $config
     */
    private $config;

    /**
     * @var Deque $routes
     */
    private $routes;

    /**
     * @var Parser $parser
     */
    private $parser;

    /**
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $this->config = array_to_map($config + self::DEFAULT_CONFIG);
        $this->routes = new Deque();
    }

    /**
     * @param  Route $route
     * @return void
     */
    public function push(Route $route)
    {
        $this->routes->push($route);
    }

    /**
     * @param  string $path URI path.
     * @return Route
     * @throws RouterException On no routes. On no match for route.
     */
    public function match(string $path) : Route
    {
        $this->parser = new Parser(
            $this->config->get('request')->get('parser'),
            new Lexer($path)
        );

        if ($this->routes->isEmpty()) {
            throw new RouterException('No routes were specified.');
        }

        while ($this->routes->count() !== 1 && !$this->parser->isEnd()) {
            $this->routes = $this->routes->filter(
                function ($route) {
                    return $route->match($this->parser);
                }
            );

            $this->parser->accept();
        }

        if ($this->routes->count() !== 1) {
            throw new RouterException('No match for route.');
        }

        return $this->routes->shift();
    }
}
