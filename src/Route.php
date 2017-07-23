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
     * @var Map $controllers
     */
    private $controllers;

    /**
     * @var Deque $parameters
     */
    private $parameters;

    /**
     * @param Map    $config
     * @param Parser $parser
     * @param Map    $controllers
     */
    public function __construct(
        Map $config,
        Parser $parser,
        Map $controllers
    ) {
        $this->configure($config);
        $this->parser = $parser;
        $this->controllers = $controllers;
        $this->parameters = new Deque();
    }

    /**
     * @param  Map  $config
     * @return void
     **/
    private function configure(Map $config) : void
    {
        check_config(new Set(self::REQUIRED_CONFIG), $config);

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
     * @return Deque $parameters
     */
    public function getParameters() : Deque
    {
        return $this->parameters;
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
