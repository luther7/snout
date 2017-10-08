<?php
namespace Snout;

use InvalidArgumentException;
use Ds\Map;
use Snout\Lexer;
use Snout\Parser;

/**
 * Request.
 */
class Request
{
    /**
     * @const array DEFAULT_CONFIG
     */
    private const DEFAULT_CONFIG = [
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
     * @var Parser $parser
     */
    private $parser;

    /**
     * @var string $method
     */
    private $method;

    /**
     * @param  string    $path
     * @param  string    $method
     * @param  array|Map $config
     */
    public function __construct(string $path, string $method, $config = [])
    {
        $config = form_config($config, self::DEFAULT_CONFIG);
        $this->method = $method;
        $this->parser = new Parser($config->get('parser'), new Lexer($path));
    }

    /**
     * @return Parser
     */
    public function getParser() : Parser
    {
        return $this->parser;
    }

    /**
     * @return string
     */
    public function getMethod() : string
    {
        return $this->method;
    }
}
