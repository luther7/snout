<?php
namespace Snout;

use OutOfRangeException;
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
     * @var ?int $index
     */
    private $index;

    /**
     * @param Map   $config
     * @param Lexer $lexer
     */
    public function __construct(Map $config, Lexer $lexer)
    {
        $this->configure($config);

        $this->lexer = $lexer;
        $this->index = null;
    }

    /**
     * @param  Map $config
     * @return void
     **/
    public function configure(Map $config) : void
    {
        check_config(new Set(self::REQUIRED_CONFIG), $config);
        $config->get('invalid')->apply(
            function ($key, $value) {
                return Token::typeConstant($value);
            }
        );

        $this->config = $config;
    }

    /**
     * @param  $index
     * @return Token
     */
    public function getToken(int $index = null) : Token
    {
        return $this->lexer->getToken($index ?? $this->index);
    }

    /**
     * @param  $index
     * @return string
     */
    public function getTokenType(int $index = null) : string
    {
        return $this->getToken($index)->getType();
    }

    /**
     * @param  $index
     * @return bool
     */
    public function tokenHasValue(int $index = null) : bool
    {
        return $this->getToken($index)->hasValue();
    }

    /**
     * @param  $index
     * @return mixed
     */
    public function getTokenValue(int $index = null)
    {
        return $this->getToken($index)->getValue();
    }

    /**
     * @return int
     */
    public function getIndex() : int
    {
        return $this->index ?? $this->lexer->getTokenCount();
    }

    /**
     * @return void
     */
    public function jump(int $index) : void
    {
        if ($index === $this->lexer->getTokenCount()) {
            return;
        }

        if ($index > $this->lexer->getTokenCount()) {
            throw new OutOfRangeException(
                "Index out of range: {$index}, "
                . "expected 0 <= x <= " . $this->lexer->getTokenCount()
            );
        }

        $this->index = $index;
    }

    /**
     * @return bool
     */
    public function isEnd() : bool
    {
        return $this->getToken()->getType() === Token::END;
    }

    /**
     * Accept token and progress Lexer. Assert optional token type and value.
     *
     * @param  string ?$type  Valid next token type.
     * @param  mixed  ?$value Valid next token value.
     * @return void
     * @throws ParserException On unexpected token.
     */
    public function accept(?string $type = null, $value = null) : void
    {
        $next_token = $this->getToken();
        $next_token_type = $next_token->getType();

        if ($this->config->get('invalid')->hasValue($next_token_type)) {
            throw new ParserException(
                "Invalid token type '{$next_token_type}'. "
                . "At char {$this->lexer->getColumn()}."
            );
        }

        if ($type !== null && $next_token_type !== $type) {
            throw new ParserException(
                "Unexpected token type '{$next_token_type}'. "
                . "Expecting token type '{$type}'. "
                . "At char {$this->lexer->getColumn()}."
            );
        }

        if ($value !== null) {
            if (!$next_token->hasValue()) {
                throw new ParserException(
                    "Expecting '{$value}'. "
                    . "At char {$this->lexer->getColumn()}."
                );
            }

            if ($next_token->getValue() !== $value) {
                throw new ParserException(
                    "Unexpected '{$next_token->getValue()}'. "
                    . "Expecting '{$value}'. "
                    . "At char {$this->lexer->getColumn()}."
                );
            }
        }

        if ($this->index !== null
            && $this->index !== $this->lexer->getTokenCount()
        ) {
            return;
        }

        $this->lexer->next();
    }

    /**
     * Accept optional token.
     *
     * @param  string ?$type  Token type.
     * @param  mixed  ?$value Token value.
     * @return void
     */
    public function optional(?string $type = null, $value = null) : void
    {
        try {
            $this->accept($type, $value);
        } catch (ParserException $e) {
            // Allow failures.
        }
    }
}
