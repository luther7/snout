<?php
namespace Snout;

use Ds\Map;
use Ds\Set;
use Snout\Exceptions\ParserException;
use Snout\Token;
use Snout\Lexer;

/**
 * Parser.
 */
class Parser
{
    /**
     * @const array REQUIRED_CONFIG
     */
    private const REQUIRED_CONFIG = [
        'invalid'
    ];

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
        check_config(new Set(self::REQUIRED_CONFIG), $config);
        $config->get('invalid')->apply(
            function ($key, $value) {
                return Token::tokenize($value);
            }
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
     * @return bool
     */
    public function isEnd() : bool
    {
        return $this->lexer->getToken() === Token::END;
    }

    /**
     * Accept and scan. Assert optional token and payload.
     *
     * @param string $token   Valid next token.
     * @param mixed  $payload Valid next payload.
     * @throws ParserException On unexpected token.
     * @return void
     */
    public function accept(string $token = null, $payload = null) : void
    {
        $next_token = $this->lexer->getToken();

        if ($this->config->get('invalid')->hasValue($next_token)) {
            throw new ParserException(
                "Invalid token '{$next_token}'. "
                . "At char {$this->lexer->getColumn()}."
            );
        }

        if ($token !== null && $next_token !== $token) {
            throw new ParserException(
                "Unexpected token '{$next_token}'. Expecting token '{$token}'. "
                . "At char {$this->lexer->getColumn()}."
            );
        }

        if ($payload !== null) {
            if (!$this->lexer->hasPayload()) {
                throw new ParserException(
                    "Expecting '{$payload}'. "
                    . "At char {$this->lexer->getColumn()}."
                );
            }

            if ($this->lexer->getPayload() !== $payload) {
                throw new ParserException(
                    "Unexpected '{$this->lexer->getPayload()}'. "
                    . "Expecting '{$payload}'. "
                    . "At char {$this->lexer->getColumn()}."
                );
            }
        }

        $this->lexer->next();
    }

    /**
     * Accept an optional token.
     *
     * @param string $token   Valid next token.
     * @param mixed  $payload Valid next payload.
     * @return void
     */
    public function optionalAccept(string $token, $payload = null) : void
    {
        try {
            $this->accept($token, $payload);
        } catch (ParserException $e) {
            // Allow.
        }
    }
}
