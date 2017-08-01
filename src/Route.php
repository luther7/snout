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
        'controllers',
        'parser',
        'parameters'
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
     * @throws InvalidArgumentException         If config is not an array or Map.
     * @param  array|Map                $config
     */
    public function __construct($config)
    {
        if (is_array($config)) {
            $config = array_to_map($config);
        } elseif (!($config instanceof Map)) {
            throw new \InvalidArgumentException('Config must be an array or \Ds\Map.');
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
        if (($this->isMatchingParameter() || $this->parseParameter())
            && $this->matchParameter($request)
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
     * @param  Map  $config
     * @return void
     **/
    private function configure(Map $config) : void
    {
        check_config(new Set(self::REQUIRED_CONFIG), $config);

        // FIXME
        $config->get('parameters')->apply(
            function ($key, $value) {
                return $value->map(
                    function ($key, $value) {
                        return Token::tokenize($value);
                    }
                );
            }
        );

        $this->config = $config;
    }

    /**
     * Check if this route is currently matching a parameter.
     *
     * @return bool
     */
    private function isMatchingParameter() : bool
    {
        // The last parameter will have a matching flag.
        return !$this->parameters->isEmpty()
            && $this->parameters->last()->get('matching', false);
    }

    /**
     * Parse an embedded parameter out of the route.
     *
     * @throws RouterException On invalid parameter type.
     * @return bool
     */
    private function parseParameter()
    {
        // Embedded parameters are of the form:
        // {name: type}
        // eg '{id: int}'
        if ($this->parser->getToken() !== Token::OPEN_BRACE) {
            return;
        }

        try {
            $this->parser->accept(Token::OPEN_BRACE);
            $this->parser->optionalAccept(Token::SPACE);
            $name = $this->parser->getPayload();
            $this->parser->accept(Token::ALPHA);
            $this->parser->optionalAccept(Token::SPACE);
            $this->parser->accept(Token::COLON);
            $this->parser->optionalAccept(Token::SPACE);
            $type = $this->parser->getPayload();
            $this->parser->accept(Token::ALPHA);
            $this->parser->optionalAccept(Token::SPACE);
            $this->parser->accept(Token::CLOSE_BRACE);
        } catch (ParserException | LexerException $e) {
            // TODO rewind.
            return false;
        }

        if (!$this->config->get('parameters')->hasKey($type)) {
            // TODO warnings.
            throw new RouterException("Invalid parameter type '{$type}'.");
        }

        $this->parameters->push(
            new Map([
                'name'     => $name,
                'type'     => $type,
                'matching' => true
            ])
        );

        return true;
    }

    /**
     * Match the request agains the current parameter.
     *
     * @param  Parser $request
     * @return bool
     */
    public function matchParameter(Parser $request) : bool
    {
        $parameter = $this->parameters->pop();
        $tokens = $this->config->get('parameters')->get($parameter->get('type'));

        if (!$tokens->hasValue($request->getToken())) {
            $parameter->remove('matching');
            $this->parameters->push($parameter);

            return false;
        }

        $parameter->put(
            'value',
            $parameter->get('value', '') . $request->getPayload()
        );

        $this->parameters->push($parameter);

        return true;
    }
}
