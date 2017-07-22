<?php
namespace Snout;

use \Snout\Exceptions\RouterException;
use \Snout\Config;
use \Snout\Lexer;
use \Snout\Parser;
use \Snout\Route;

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
        'parser' => [
            'invalid' => [
                'SPACE',
                'TAB',
                'NEW_LINE',
                'CARRIAGE_RETURN'
            ]
        ]
    ];

    /**
     * @var Config $config
     */
    private $config;

    /**
     * @var array $routes
     */
    private $routes;

    /**
     * @var int $route_count
     */
    private $route_count;

    /**
     * @var Parser $parser
     */
    private $parser;

    /**
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $this->config = new Config($config + self::DEFAULT_CONFIG);
        $this->routes = [];
        $this->route_count = 0;
    }

    /**
     * @param string $path URI path.
     * @param array  $map  Map of HTTP methods to controller closures.
     * @return void
     */
    public function route(string $path, array $map)
    {
        $this->routes[] = new Route($path, $map);
        $this->route_count++;
    }

    /**
     * @param string $path   URI path.
     * @param array  $method HTTP method.
     * @throws RouterException On no routes.
     * @return void
     */
    public function match(string $path, string $method)
    {
        $this->parser = new Parser($this->config, new Lexer($path));

        if ($this->route_count === 0) {
            throw new RouterException('No routes where specified.');
        }

        $remaining = $this->routes;
        $remaining_count = $this->route_count;

        while ($remaining_count !== 1) {
            // if (!$this->parser->isEOF()) {
            // }

            foreach ($remaining as $index => $route) {
                if (!$route->match($this->parser)) {
                    unset($remaining[$index]);
                    $remaining_count--;
                }
            }

            $this->parser->accept();
        }
    }
}