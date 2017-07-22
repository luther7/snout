<?php
namespace Snout;

use \Ds\Map;
use \Ds\OutOfBoundsException;
use \Snout\Exceptions\ConfigurationException;
use \Snout\Exceptions\ParserException;
use \Snout\Token;
use \Snout\Lexer;

/**
 * Parser.
 */
class Parser
{
    /**
     * @var Lexer $lexer
     */
    private $lexer;

    /**
     * @var Map $config
     */
    private $config;

    /**
     * @param Map   $config
     * @param Lexer $lexer
     */
    public function __construct(Map $config, Lexer $lexer)
    {
        $this->configure($config);
        $this->lexer = $lexer;
    }

    /**
     * @param Map $config
     * @return void
     **/
    public function configure(Map $config) : void
    {
        try {
            $config = $config->get('parser');
        } catch (OutOfBoundsException $e) {
            throw new ConfigurationException('parser');
        }

        $config->put(
            'invalid',
            $config->get('invalid')->map(
                function ($key, $value) {
                    return constant("\Snout\Token::{$value}");
                }
            )
        );

        $this->config = $config;
    }

    /**
     * @return string Current token.
     */
    public function getToken() : string
    {
        return $this->lexer->getToken();
    }

    /**
     * @return bool Payload flag.
     */
    public function hasPayload() : bool
    {
        return $this->lexer->hasPayload();
    }

    /**
     * @return string Current payload.
     */
    public function getPayload() : string
    {
        return $this->lexer->getPayload();
    }

    /**
     * Accept and scan.
     *
     * @return void
     */
    public function accept() : void
    {
        $token = $this->lexer->getToken();
        $this->checkInvalid($token);
        $this->lexer->next();
    }

    /**
     * Accept token and scan.
     *
     * @param string $valid Valid next token.
     * @throws ParserException On unexpected token.
     * @return void
     */
    public function acceptToken(string $valid) : void
    {
        $token = $this->lexer->getToken();
        $this->checkInvalid($token);

        if ($token !== $valid) {
            $column = $this->lexer->getColumn();

            throw new ParserException(
                "Unexpected token '{$token}'. Expecting token '{$valid}'. "
                . "At char {$column}."
            );
        }

        $this->lexer->next();
    }

    /**
     * Check if token is invalid.
     *
     * @param string $token Token.
     * @throws ParserException On invalid token.
     * @return void
     */
    private function checkInvalid(string $token) : void
    {
        if (!$this->config->get('invalid')->hasValue($token)) {
            return;
        }

        $column = $this->lexer->getColumn();

        throw new ParserException(
            "Invalid token '{$token}'. At char {$column}."
        );
    }
}
