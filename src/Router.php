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
     * @param  string $path              URI path.
     * @param  string $method            HTTP method.
     * @param  Parser $request           Request parser.
     * @param  Map    $parent_parameters Embedded parameters from possible
     *                                   parent router.
     * @return array
     * @throws RouterException On no routes. On no match for route.
     */
    public function match(
        string $path,
        string $method,
        Parser $request = null,
        Map $parent_parameters = null
    ) : array {
        if ($this->routes->isEmpty()) {
            throw new RouterException('No routes were specified.');
        }

        if ($request === null) {
            $request = new Parser(
                $this->config->get('request')->get('parser'),
                new Lexer($path)
            );
        }

        // Eliminate routes until only one remains.
        while ($this->routes->count() !== 1) {
            if ($request->isComplete()) {
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

        // Matching the request to the route as far as possible.
        while (!$request->isComplete() && $route->match($request)) {
            $request->accept();
        }

        // Merge possible embedded parameters from parent routing.
        $parameters = $route->getParameters();
        if ($parent_parameters !== null) {
            // Check for duplicates.
            $duplicates = $parent_parameters->intersect($parameters)->keys();

            if (!$duplicates->isEmpty()) {
                throw new RouterException(
                    "Duplicate embedded parameter name(s) '"
                    . $missing->join("', '")
                    . "'. In route {$route->getName()} and parent route."
                );
            }

            $parameters = $parent_parameters->merge($parameters);
        }

        // If the request and route are fully matched return the controller.
        if ($request->isComplete() && $route->isComplete()) {
            return [
                $route->getController($method),
                $parameters
            ];
        }

        // If the request is not fully matched, then the route must be and must
        // be a route to a sub-controller.
        if (!$request->isComplete()
            && $route->isComplete()
            && $route->hasSubRouter()
        ) {
            // if ($route->hasController($method)) {
            //     $route->runController($method);
            // }

            return $route->getSubRouter()->match(
                $path,
                $method,
                $request,
                $parameters
            );
        }

        throw new RouterException(
            "No match for path '{$path}'. Incomplete match with "
            . "'{$route->getName()}'."
        );
    }
}
