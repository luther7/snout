<?php
namespace Snout;

use InvalidArgumentException;
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
    private const REQUIRED_CONFIG = ['invalid'];

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
    private function configure(Map $config) : void
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
     * @return int
     */
    public function getIndex() : int
    {
        return $this->index ?? ($this->getLexerIndex());
    }

    /**
     * @param  $index
     * @return Token
     */
    public function getToken(int $index = null) : Token
    {
        return $this->lexer->getToken($index ?? $this->getIndex());
    }

    /**
     * Jump to the token at an index. Allows backtracking.
     *
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
    public function isComplete() : bool
    {
        return $this->getToken()->getType() === Token::END;
    }

    /**
     * Accept token and advance Lexer. Assert optional token type and value.
     *
     * @param  mixed ?$type  String, Token or null.
     * @param  mixed ?$value
     * @return void
     * @throws ParserException On unexpected token.
     */
    public function accept($type = null, $value = null) : void
    {
        if ($type instanceof Token) {
            if ($type->hasValue()) {
                $value = $type->getValue();
            }

            $type = $type->getType();
        } elseif (!is_string($type) && $type !== null) {
            throw new \InvalidArgumentException(
                'First argument must be a string, instance of \Snout\Token '
                . 'or null.'
            );
        }

        $next = $this->getToken();
        $next_type = $next->getType();

        if ($this->config->get('invalid')->hasValue($next_type)) {
            throw new ParserException(
                "Invalid token type '{$next_type}'. "
                . "At char {$this->lexer->getColumn()}."
            );
        }

        if ($type !== null && $next_type !== $type) {
            throw new ParserException(
                "Unexpected token type '{$next_type}'. "
                . "Expecting token type '{$type}'. "
                . "At char {$this->lexer->getColumn()}."
            );
        }

        if ($value !== null) {
            if (!$next->hasValue()) {
                throw new ParserException(
                    "Expecting '{$value}'. "
                    . "At char {$this->lexer->getColumn()}."
                );
            }

            if ($next->getValue() !== $value) {
                throw new ParserException(
                    "Unexpected '{$next->getValue()}'. "
                    . "Expecting '{$value}'. "
                    . "At char {$this->lexer->getColumn()}."
                );
            }
        }

        // If the index is not null then there was a jump.
        if ($this->index !== null) {
            if ($this->index < $this->getLexerIndex()) {
                $this->index++;

                // Avoid advancing the Lexer.
                return;
            }

            // Reset the index - parsing has caught up with the Lexer.
            $this->index = null;
        }

        $this->lexer->next();
    }

    /**
     * Optionally accept a token.
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
            // Allow Parser failures.
        }
    }

    /**
     * @return void
     */
    public function debug() : string
    {
        return $this->lexer->debug();
    }

    /**
     * Get the Lexer's index. It may differ to the Parser's after a jump.
     *
     * @return int
     */
    private function getLexerIndex() : int
    {
        return $this->lexer->getTokenCount() - 1;
    }
}
