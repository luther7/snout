<?php
namespace Snout;

use Ds\Vector;
use Ds\Map;
use Ds\Set;
use Snout\Exceptions\ConfigurationException;
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
    private const REQUIRED_CONFIG = ['path'];

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
     * @var Map $parameters
     */
    private $parameters;

    /**
     * @var ?Map $parameter An embedded parameter currently being parsed.
     */
    private $parameter;

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

        $this->parser = new Parser(
            $this->config->get('parser'),
            new Lexer($this->config->get('path'))
        );

        $this->parameters = new Map();
        $this->parameter = null;
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
     * @return Map
     */
    public function getParameters() : Map
    {
        $this->saveParameter();

        return $this->parameters;
    }

    /**
     * @param  string $method
     * @return bool
     */
    public function hasController(string $method) : bool
    {
        return $this->config->get('controllers', false)
               && $this->config->get('controllers')->hasKey($method);
    }

    /**
     * @param  string $method
     * @return void
     */
    public function runController(string $method) : void
    {
        if (!$this->hasController($method)) {
            throw new RouterException("Method '{$method}' not allowed.");
        }

        $this->config->get('controllers')->get($method)($this->getParameters());
    }

    /**
     * @return bool
     */
    public function hasSubRouter() : bool
    {
        return $this->config->hasKey('sub_router');
    }

    /**
     * @return Router
     */
    public function getSubRouter() : Router
    {
        return $this->config->get('sub_router');
    }

    /**
     * @return bool
     */
    public function isComplete() : bool
    {
        return $this->parser->isEnd();
    }

    /**
     * Match this route against the request.
     *
     * @param  Parser $request
     * @return bool
     */
    public function match(Parser $request) : bool
    {
        // If there is a parameter currently being parsed,
        // or one to parse, attempt to match the request to that parameter.
        if (($this->parameter !== null || $this->parseParameter())
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

        if (!$config->hasKey('controllers') && !$config->hasKey('sub_router')) {
            throw new ConfigurationException(
                "Invalid configuration. Require option 'controllers' or "
                . "'sub_router'"
            );
        }

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
        $save_point = $this->parser->getIndex();

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
        } catch (ParserException $e) {
            $this->parser->jump($save_point);

            return false;
        }

        if (!$this->config->get('parameters')->hasKey($type)) {
            throw new RouterException("Invalid parameter type '{$type}'.");
        }

        $this->parameter = new Map();
        $this->parameter->put('name', $name);
        $this->parameter->put('type', $type);
        $this->parameter->put('values', new Vector());

        return true;
    }

    /**
     * Match the request against the current parameter.
     *
     * @param  Parser $request
     * @return bool
     */
    private function matchParameter(Parser $request) : bool
    {
        $token_types = $this->config->get('parameters')->get(
            $this->parameter->get('type')
        );

        // If the next token in the request matches the type of the parameter.
        if ($token_types->hasValue($request->getTokenType())) {
            // Add either the token value or the lexeme.
            $parameter_value = $request->tokenHasValue()
                             ? $request->getTokenValue()
                             : $request->getTokenLexeme();

            $this->parameter->get('values')->push($parameter_value);

            return true;
        }

        // If the types did not match then save the parsing parameter.
        $this->saveParameter();
        return false;
    }

    public function saveParameter() : void
    {
        if ($this->parameter === null) {
            return;
        }

        $this->parameters->put(
            $this->parameter->get('name'),
            new Parameter(
                $this->parameter->get('name'),
                $this->parameter->get('type'),
                $this->parameter->get('values')->join()
            )
        );

        $this->parameter = null;
    }
}
