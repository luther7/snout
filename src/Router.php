<?php
namespace Snout;

use Ds\Map;
use Ds\Set;
use Snout\Exceptions\RouterException;
use Snout\Request;
use Snout\Route;

/**
 * Router.
 */
class Router
{
    /**
     * @var Set $routes
     */
    private $routes;

    /**
     * @param array $config
     */
    public function __construct()
    {
        $this->routes = new Set();
    }

    /**
     * @param  Route $route
     * @return void
     */
    public function push(Route $route) : void
    {
        $this->routes->add($route);
    }

    /**
     * @param  Request $request
     * @return Route
     * @throws RouterException On no routes. On no match for route.
     */
    public function match(Request &$request) : Route
    {
        if ($this->routes->isEmpty()) {
            throw new RouterException(
                "No match for request '{$request->getPath()}' - "
                . 'No routes were specified.'
            );
        }

        // Eliminate routes until only one remains.
        while ($this->routes->count() !== 1) {
            if ($request->getParser()->isComplete()) {
                throw new RouterException(
                    "No match for request '{$request->getPath()}' - "
                    . 'Multiple possible routes.'
                );
            }

            $this->routes = $this->routes->filter(
                function ($route) use (&$request) {
                    return $route->match($request->getParser());
                }
            );

            $request->getParser()->accept();
        }

        $route = $this->routes->first();

        // Matching the request to the route as far as possible.
        while (!$request->getParser()->isComplete()
            && $route->match($request->getParser())
        ) {
            $request->getParser()->accept();
        }

        // If the request and route are fully matched return the controller.
        if ($request->getParser()->isComplete() && $route->isComplete()) {
            return $route;
        }

        // If the request is not fully matched, then the route must be, and must
        // be a route to a sub-controller.
        if (!$request->getParser()->isComplete()
            && $route->isComplete()
            && $route->hasSubRouter()
        ) {
            return $route;
        }

        throw new RouterException(
            "No match for request '{$request->getPath()}' - "
            . "Incomplete match with route '{$route->getName()}'."
        );
    }

    /**
     * @param  Request $request
     * @param  mixed   $controller_args
     * @param  ?Map    $parameters      Parameters passed from parent router.
     * @return void
     */
    public function run(
        Request $request,
        $controller_args = null,
        Map $parameters = null
    ) : void {
        $route = $this->match($request);

        if ($parameters === null) {
            $parameters = $route->getParameters();
        } else {
            $duplicates = $parameters->intersect($route->getParameters())->keys();

            if (!$duplicates->isEmpty()) {
                throw new RouterException(
                    "Duplicate embedded parameters: '"
                    . $duplicates->join("', '") . "'."
                );
            }

            $parameters->putAll($route->getParameters());
        }

        if ($route->hasController($request->getMethod())) {
            $controller = $route->getController($request->getMethod());

            if ($controller_args == null) {
                $controller($parameters);
            } else {
                $controller($parameters, $controller_args);
            }
        }

        if ($route->hasSubRouter()) {
            $route->getSubRouter()->run(
                $request,
                $controller_args,
                $parameters
            );
        }
    }
}
