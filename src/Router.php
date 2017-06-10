<?php
namespace Snout;

use \Snout\Lexer;
use \Snout\Router;

/**
 * Router.
 */
class Router
{
    /**
     * @var array Config.
     */
    private $config;

    /**
     * @var Parser Parser.
     */
    private $parser;

    /**
     * @param array $config Client configuration settings.
     */
    public function __construct(array $config = [])
    {
        $this->config = $config;
    }

    /**
     * @param string $path   URI path.
     * @param array  $method HTTP method.
     *
     * @return void
     */
    public function route(string $path, string $method)
    {
        $this->parser = new Parser(new Lexer($path), $method);
    }
}