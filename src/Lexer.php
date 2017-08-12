<?php
namespace Snout;

use Ds\Vector;
use Snout\Exceptions\LexerException;
use Snout\StringIterator;
use Snout\Token;

/**
 * Lexer.
 */
class Lexer
{
    /**
     * @var StringIterator $source
     */
    private $source;

    /**
     * @var Vector $tokens
     */
    private $tokens;

    /**
     * @var int $column Char column of last consumed token.
     */
    private $column;

    /**
     * @param string $source
     */
    public function __construct(string $source)
    {
        $this->source = new StringIterator($source);
        $this->tokens = new Vector();
        $this->column = 1;

        $this->next();
    }

    /**
     * @param  $index
     * @return Token
     */
    public function getToken(int $index = null) : Token
    {
        return $index === null
               ? $this->tokens->last()
               : $this->tokens->get($index);
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
     * @return int Token count.
     */
    public function getTokenCount() : int
    {
        return $this->tokens->count();
    }

    /**
     * @return int Char count.
     */
    public function getCharCount() : int
    {
        return $this->source->key();
    }

    /**
     * @return int Char column of last consumed token.
     */
    public function getColumn() : int
    {
        return $this->column;
    }

    /**
     * @return void
     * @throws LexerException On unexpected character.
     */
    public function next() : void
    {
        if (!$this->source->valid()) {
            $this->column = $this->source->key();
            $this->tokens->push(new Token(TOKEN::END));

            return;
        }

        $this->column = $this->source->key() + 1;

        $lexeme = $this->scan(
            function ($char) {
                return ctype_digit($char);
            }
        );

        if ($lexeme !== '') {
            $this->tokens->push(
                new Token(Token::DIGIT, $lexeme, (int) $lexeme)
            );

            return;
        }

        $lexeme = $this->scan(
            function ($char) {
                return ctype_alpha($char);
            }
        );

        if ($lexeme !== '') {
            $this->tokens->push(
                new Token(Token::ALPHA, $lexeme, $lexeme)
            );

            return;
        }

        $char = $this->source->current();
        $this->source->next();

        switch ($char) {
            case '/':
                $this->tokens->push(new Token(Token::FORWARD_SLASH, $char));
                return;

            case '_':
                $this->tokens->push(new Token(Token::UNDERSCORE, $char));
                return;

            case '-':
                $this->tokens->push(new Token(Token::HYPHEN, $char));
                return;

            case '.':
                $this->tokens->push(new Token(Token::PERIOD, $char));
                return;

            case ':':
                $this->tokens->push(new Token(Token::COLON, $char));
                return;

            case '{':
                $this->tokens->push(new Token(Token::OPEN_BRACE, $char));
                return;

            case '}':
                $this->tokens->push(new Token(Token::CLOSE_BRACE, $char));
                return;

            case '\\':
                $this->tokens->push(new Token(Token::BACK_SLASH, $char));
                return;

            case ' ':
                $this->tokens->push(new Token(Token::SPACE, $char));
                return;

            case "\t":
                $this->tokens->push(new Token(Token::TAB, $char));
                return;

            case "\n":
                $this->tokens->push(new Token(Token::NEW_LINE, $char));
                return;

            case "\r":
                $this->tokens->push(new Token(Token::CARRIAGE_RETURN, $char));
                return;

            default:
                throw new LexerException(
                    "Unexpected character: '{$char}'. "
                    . "At {$this->getCharCount()}."
                );
        }
    }

    /**
     * Scan a lexeme - a sequence of chars satisfying a check.
     *
     * @param  callable $check  Closure to check chars while scanning.
     * @param  string   $lexeme Scanned lexeme.
     * @return string           The scanned lexeme.
     */
    private function scan(callable $check, string $lexeme = '') : string
    {
        if (!$this->source->valid()) {
            return $lexeme;
        }

        $char = $this->source->current();

        if ($check($char)) {
            $lexeme .= $char;
            $this->source->next();

            return $this->scan($check, $lexeme);
        }

        return $lexeme;
    }
}
