<?php
namespace Snout;

use \Snout\Exceptions\ParserException;
use \Snout\Token;
use \Snout\Config;
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
     * @var array $invalid Invalid tokens.
     */
    private $invalid;

    /**
     * @param Config $config
     * @param Lexer  $lexer
     */
    public function __construct(Config $config, Lexer $lexer)
    {
        $this->configure($config);
        $this->lexer = $lexer;
    }

    /**
     * @param Config $config
     * @return void
     **/
    public function configure(config $config) : void
    {
        $parser_config = $config->get('parser', true);

        $this->invalid = array_map(
            function ($t) {
                return constant("\Snout\Token::{$t}");
            },
            $parser_config['invalid']
        );
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
        if (!in_array($token, $this->invalid)) {
            return;
        }

        $column = $this->lexer->getColumn();

        throw new ParserException(
            "Invalid token '{$token}'. At char {$column}."
        );
    }
}
