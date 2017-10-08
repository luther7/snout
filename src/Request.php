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
     * @throws InvalidArgumentException If config is not an array or Map.
     */
    public function __construct(string $path, string $method, $config = null)
    {
        if ($config === null) {
            $config = new Map();
        }

        if (is_array($config)) {
            $config = array_to_map($config);
        } elseif (!($config instanceof Map)) {
            throw new InvalidArgumentException(
                '$config must be an array or an instance of \Ds\Map.'
            );
        }

        $default_config = array_to_map(self::DEFAULT_CONFIG);
        $config = $default_config->merge($config);
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
