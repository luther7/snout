<?php
namespace Snout;

use \Snout\Exceptions\ParserException;
use \Snout\Exceptions\LexerException;
use \Snout\Parser;

/**
 * Route.
 */
class Route
{
    /**
     * @const array $default_config
     */
    private const CONFIG = [
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
     * @var string $path
     */
    private $path;

    /**
     * @var array $map
     */
    private $map;

    /**
     * @var Parser $parser
     */
    private $parser;

    /**
     * @var array $parameters
     */
    private $parameters;

    /**
     * @var bool $matching_parameter
     */
    private $matching_parameter;

    /**
     * @var string $path
     * @var array  $map
     */
    public function __construct(string $path, array $map)
    {
        $this->path = $path;
        $this->map = $map;
        $this->parser = null;
        $this->parameters = [];
        $this->matching_parameter = false;
    }

    public function match(Parser $request) : bool
    {
        if ($this->parser === null) {
            $this->parser = new Parser(self::CONFIG, $this->path);
        }

        try {
            if ($request->hasPayload()) {
                if ($this->matchParameter($request)) {
                    return true;
                }

                // if (!$this->parser->hasPayload()) {
                //     return false;
                // }

                // if (!$this->parser->getPayload() === $request->getPayload) {
                //     return false;
                // }
            }

            $this->parser->accept($request->getToken());
        } catch (ParserException $e) {
            return false;
        }
    }

    private function matchParameter(Parser $request)
    {
        if ($this->matchingParameter)
    }
}
