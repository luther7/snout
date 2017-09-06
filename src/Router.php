<?php
namespace Snout;

use Ds\Map;
use Ds\Set;
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
     * @var Set $routes
     */
    private $routes;

    /**
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $this->config = array_to_map($config + self::DEFAULT_CONFIG);
        $this->routes = new Set();
    }

    /**
     * @param  Route $route
     * @return void
     */
    public function push(Route $route)
    {
        $this->routes->add($route);
    }

    /**
     * @param  string $path       URI path.
     * @param  string $method     HTTP method.
     * @param  Parser $request     Request request.
     * @param  Map    $parameters Embedded parameters.
     * @return void
     * @throws RouterException On no routes. On no match for route.
     */
    public function run(
        string $path,
        string $method,
        Parser $request = null,
        Map $parameters = null
    ) : void {
        if ($this->routes->isEmpty()) {
            throw new RouterException('No routes were specified.');
        }

        if ($parameters === null) {
            $parameters = new Map();
        }

        if ($request === null) {
            $request = new Parser(
                $this->config->get('request')->get('parser'),
                new Lexer($path)
            );
        }

        // Narrow the routes down to a single one.
        while ($this->routes->count() !== 1) {
            if ($request->isEnd()) {
                throw new RouterException(
                    "No match for path '{$path}'. Multiple possible routes."
                );
            }

            $this->routes = $this->routes->filter(
                function ($route) use (&$request) {
                    return $route->match($request);
                }
            );

            $request->accept();
        }

        $route = $this->routes->first();

        // If the request is not fully matched, keep matching the route to it.
        while (!$request->isEnd()) {
            if (!$route->match($request)) {
                // If they do not match check for a sub-router.
                if (!$route->hasSubRouter()
                    || !$route->isComplete()
                ) {
                    throw new RouterException(
                        "No match for path '{$path}'. Incomplete match with "
                        . "'{$route->getPath()}'."
                    );
                }

                if ($route->hasController($method)) {
                    $route->runController($method);
                }

                $route->getSubRouter()->run($path, $method, $request);

                return;
            }

            $request->accept();
        }

        if (!$route->isComplete($request)) {
            throw new RouterException(
                "No match for path '{$path}'. Incomplete match with "
                . "'{$route->getPath()}'."
            );
        }

        $route->runController($method);
    }
}
