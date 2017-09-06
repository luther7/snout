<?php
namespace Snout;

use InvalidArgumentException;
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
     * @var ?Map $incomplete_parameter Possible incomplete parameter being
     *                                 currently matched.
     */
    private $incomplete_parameter;

    /**
     * @param  array|Map $config
     * @throws InvalidArgumentException If config is not an array or Map.
     */
    public function __construct($config)
    {
        if (is_array($config)) {
            $config = array_to_map($config);
        } elseif (!($config instanceof Map)) {
            throw new InvalidArgumentException(
                '$config must be an array or instance of \Ds\Map.'
            );
        }

        $this->configure($config);

        $this->parser = new Parser(
            $this->config->get('parser'),
            new Lexer($this->config->get('path'))
        );

        $this->parameters = new Map();
        $this->incomplete_parameter = null;
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
        $this->saveIncompleteParameter();

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
     * @return mixed  $controller
     * @throws RouterException On no controller for method.
     */
    public function getController(string $method)
    {
        if (!$this->hasController($method)) {
            throw new RouterException("No controller for method '{$method}'.");
        }

        return $this->config->get('controllers')->get($method);
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
     * @throws RouterException On no sub-router.
     */
    public function getSubRouter() : Router
    {
        if (!$this->hasSubRouter()) {
            throw new RouterException("Sub-router not found.");
        }

        return $this->config->get('sub_router');
    }

    /**
     * @return bool
     */
    public function isComplete() : bool
    {
        return $this->parser->isComplete();
    }

    /**
     * Match this route against the request.
     *
     * @param  Parser $request
     * @return bool
     */
    public function match(Parser $request) : bool
    {
        // If currently matching an incomplete parameter,
        // or an embedded parameter is next in this route,
        // attempt to match the request to that parameter.
        if (($this->incomplete_parameter !== null
            || $this->parseEmbeddedParameter())
            && $this->matchToIncompleteParameter($request)
        ) {
            return true;
        }

        try {
            $this->parser->accept($request->getToken());
        } catch (ParserException $e) {
            return false;
        }

        return true;
    }

    /**
     * @return void
     */
    public function debug() : string
    {
        return $this->parser->debug();
    }

    /**
     * @param  Map  $config
     * @return void
     * @throws ConfigurationException On no controllers or sub-router.
     **/
    private function configure(Map $config) : void
    {
        $default_config = array_to_map(self::DEFAULT_CONFIG);
        $config = $default_config->merge($config);

        check_config(new Set(self::REQUIRED_CONFIG), $config);
        if (!$config->hasKey('controllers') && !$config->hasKey('sub_router')) {
            throw new ConfigurationException(
                "Invalid configuration. Require option 'controllers' or "
                . "'sub_router'."
            );
        }

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
     * @throws RouterException On invalid parameter type or duplicate
     *                         parameter name.
     */
    private function parseEmbeddedParameter()
    {
        // Embedded parameters are of the form:
        // {name: type}
        // eg '{id: int}'
        $save_point = $this->parser->getIndex();

        try {
            $this->parser->accept(Token::OPEN_BRACE);
            $this->parser->optional(Token::SPACE);
            $name = $this->parser->getToken()->getValue();
            $this->parser->accept(Token::ALPHA);
            $this->parser->optional(Token::SPACE);
            $this->parser->accept(Token::COLON);
            $this->parser->optional(Token::SPACE);
            $type = $this->parser->getToken()->getValue();
            $this->parser->accept(Token::ALPHA);
            $this->parser->optional(Token::SPACE);
            $this->parser->accept(Token::CLOSE_BRACE);
        } catch (ParserException $e) {
            $this->parser->jump($save_point);

            return false;
        }

        if (!$this->config->get('parameters')->hasKey($type)) {
            throw new RouterException(
                "Invalid embedded parameter type '{$type}'. "
                . "In route {$this->getName()}."
            );
        }

        if ($this->parameters->hasKey($name)) {
            throw new RouterException(
                "Duplicate embedded parameter name '{$name}'. "
                . "In route {$this->getName()}."
            );
        }

        $this->incomplete_parameter = new Map([
            'name'        => $name,
            'type'        => $type,
            'values'      => new Vector(),
            'token_types' => $this->config->get('parameters')->get($type)
        ]);

        return true;
    }

    /**
     * Match the request against the current incomplete parameter.
     *
     * @param  Parser $request
     * @return bool
     */
    private function matchToIncompleteParameter(Parser $request) : bool
    {
        // If the current request token type matches the parameter.
        $valid = $this->incomplete_parameter->get('token_types')->hasValue(
            $request->getToken()->getType()
        );

        // If the types did not match then the request has moved past the
        // parameter. Save it. Return false - the current request token is not
        // part of the parameter.
        if (!$valid) {
            $this->saveIncompleteParameter();

            return false;
        }

        // Add either the token value or the lexeme.
        $this->incomplete_parameter->get('values')->push(
            $request->getToken()->hasValue()
            ? $request->getToken()->getValue()
            : $request->getToken()->getLexeme()
        );

        return true;
    }

    /**
     * Save the current incomplete parameter.
     *
     * @param  Parser $request
     * @return bool
     */
    private function saveIncompleteParameter() : void
    {
        if ($this->incomplete_parameter === null) {
            return;
        }

        $this->parameters->put(
            $this->incomplete_parameter->get('name'),
            new Parameter(
                $this->incomplete_parameter->get('name'),
                $this->incomplete_parameter->get('type'),
                $this->incomplete_parameter->get('values')->join()
            )
        );

        $this->incomplete_parameter = null;
    }
}
