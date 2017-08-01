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
     * @const array $default_config
     */
    const DEFAULT_CONFIG = [
        'delimiter' => 'FORWARD_SLASH',
        'request' => [
            'parser' => [
                'invalid' => [
                    'SPACE',
                    'TAB',
                    'NEW_LINE',
                    'CARRIAGE_RETURN'
                ]
            ]
        ],
        'route' => [
            'parser' => [
               'invalid' => [
                   'TAB',
                   'NEW_LINE',
                   'CARRIAGE_RETURN'
               ]
            ],
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
        ],
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
     * @param  string $path        URI path.
     * @param  array  $controllers Map of HTTP methods to controller closures.
     * @return void
     */
    public function route(string $path, array $controllers, ?string $name = null) : void
    {
        $config = $this->config->get('route');
        $config->put('name', $name);

        $this->routes->push(
            $config,
            new Route(
                new Parser(
                    $this->config->get('route')->get('parser'),
                    new Lexer($path)
                ),
                new Map($controllers)
            )
        );
    }

    /**
     * @param  string          $path URI path.
     * @throws RouterException       On no routes. On no match for route.
     * @return Route
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
            $this->routes->filter(
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