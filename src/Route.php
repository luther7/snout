<?php
namespace Snout;

use Ds\Map;
use Ds\Deque;
use Ds\Set;
use Snout\Exceptions\LexerException;
use Snout\Exceptions\ParserException;
use Snout\Exceptions\RouterException;
use Snout\Token;
use Snout\Parser;
use Snout\Parameter;

/**
 * Route.
 */
class Route
{
    /**
     * @const array REQUIRED_CONFIG
     */
    private const REQUIRED_CONFIG = [
        'path',
        'controllers'
    ];

    /**
     * @const array DEFAULT_CONFIG
     */
    private const DEFAULT_CONFIG = [
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
    ];

    /**
     * @var Map $config
     */
    private $config;

    /**
     * @var Parser $parser
     */
    private $parser;

    /**
     * @var Deque $parameters
     */
    private $parameters;

    /**
     * @param  array|Map $config
     * @throws InvalidArgumentException If config is not an array or Map.
     */
    public function __construct($config)
    {
        if (is_array($config)) {
            $config = array_to_map($config);
        } elseif (!($config instanceof Map)) {
            throw new \InvalidArgumentException(
                '$config must be an array or instance of \Ds\Map.'
            );
        }

        $this->configure($config);

        $this->parameters = new Deque();
        $this->parser = new Parser(
            $this->config->get('parser'),
            new Lexer($this->config->get('path'))
        );
    }

    /**
     * @return string
     */
    public function getPath() : string
    {
        return $this->config->get('path');
    }

    /**
     * @return string
     */
    public function getName() : string
    {
        return $this->config->get('name', $this->getPath());
    }

    /**
     * @return Deque
     */
    public function getParameters() : Deque
    {
        return $this->parameters;
    }

    /**
     * @return Map
     */
    public function getController() : Map
    {
        return $this->config->get('controllers');
    }

    /**
     * @return void
     */
    public function runController(string $method)
    {
        if (!$this->config->get('controllers')->hasKey($method)) {
            throw new RouterException("Method {$method} not allowed.");
        }

        $this->config->get('controllers')->get($method)($this->parameters);
    }

    /**
     * Match this route against the request.
     *
     * @param  Parser $request
     * @return bool
     */
    public function match(Parser $request) : bool
    {
        if ((!$this->parameters->isEmpty()
           && $this->parameters->last()->isMatching()
           || $this->parseParameter())
           && $this->matchParameter($request)
        ) {
            return true;
        }

        try {
            $this->parser->accept($request->getTokenType());
        } catch (ParserException $e) {
            return false;
        }

        return true;
    }

    /**
     * @param  Map  $config
     * @return void
     **/
    private function configure(Map $config) : void
    {
        $default_config = array_to_map(self::DEFAULT_CONFIG);
        $config = $default_config->merge($config);

        check_config(new Set(self::REQUIRED_CONFIG), $config);

        // FIXME
        $config->get('parameters')->apply(
            function ($key, $value) {
                return $value->map(
                    function ($key, $value) {
                        return Token::typeConstant($value);
                    }
                );
            }
        );

        $this->config = $config;
    }

    /**
     * Parse an embedded parameter out of the route.
     *
     * @return bool
     * @throws RouterException On invalid parameter type.
     */
    private function parseParameter()
    {
        // Embedded parameters are of the form:
        // {name: type}
        // eg '{id: int}'
        if ($this->parser->getTokenType() !== Token::OPEN_BRACE) {
            return false;
        }

        try {
            $this->parser->accept(Token::OPEN_BRACE);
            $this->parser->optional(Token::SPACE);
            $name = $this->parser->getTokenValue();
            $this->parser->accept(Token::ALPHA);
            $this->parser->optional(Token::SPACE);
            $this->parser->accept(Token::COLON);
            $this->parser->optional(Token::SPACE);
            $type = $this->parser->getTokenValue();
            $this->parser->accept(Token::ALPHA);
            $this->parser->optional(Token::SPACE);
            $this->parser->accept(Token::CLOSE_BRACE);
        } catch (ParserException | LexerException $e) {
            // TODO rewind.
            return false;
        }

        if (!$this->config->get('parameters')->hasKey($type)) {
            // TODO warnings.
            throw new RouterException("Invalid parameter type '{$type}'.");
        }

        $this->parameters->push(new Parameter($name, $type));

        return true;
    }

    /**
     * Match the request against the current parameter.
     *
     * @param  Parser $request
     * @return bool
     */
    public function matchParameter(Parser $request) : bool
    {
        $parameter = $this->parameters->pop();
        $token_types = $this->config->get('parameters')->get($parameter->getType());

        if (!$token_types->hasValue($request->getTokenType())) {
            $parameter->matched();
            $this->parameters->push($parameter);

            return false;
        }

        $parameter->addValue($request->getTokenValue());
        $this->parameters->push($parameter);

        return true;
    }
}
