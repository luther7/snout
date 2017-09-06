<?php
namespace Snout;

use Ds\Vector;
use Snout\Exceptions\ParserException;
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
     * @var int $column Column of the last character of the last consumed token.
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
     * @return int
     */
    public function getTokenCount() : int
    {
        return $this->tokens->count();
    }

    /**
     * @return int
     */
    public function getCharCount() : int
    {
        return $this->source->key();
    }

    /**
     * @return int Column of the last character of the last consumed token.
     */
    public function getColumn() : int
    {
        return $this->column;
    }

    /**
     * @return void
     * @throws ParserException On unexpected character.
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
                $type = Token::FORWARD_SLASH;
                break;

            case '_':
                $type = Token::UNDERSCORE;
                break;

            case '-':
                $type = Token::HYPHEN;
                break;

            case '.':
                $type = Token::PERIOD;
                break;

            case ':':
                $type = Token::COLON;
                break;

            case '{':
                $type = Token::OPEN_BRACE;
                break;

            case '}':
                $type = Token::CLOSE_BRACE;
                break;

            case '[':
                $type = Token::OPEN_BRACKET;
                break;

            case ']':
                $type = Token::CLOSE_BRACKET;
                break;

            case '\\':
                $type = Token::BACK_SLASH;
                break;

            case ' ':
                $type = Token::SPACE;
                break;

            case "\t":
                $type = Token::TAB;
                break;

            case "\n":
                $type = Token::NEW_LINE;
                break;

            case "\r":
                $type = Token::CARRIAGE_RETURN;
                break;

            default:
                throw new ParserException(
                    "Unexpected character: '{$char}'. "
                    . "At {$this->getCharCount()}."
                );
        }

        $this->tokens->push(new Token($type, $char));
    }

    /**
     * @return void
     */
    public function debug() : string
    {
        $values = $this->tokens->map(
            function ($t) {
                return $t->hasValue() ? $t->getValue() : $t->getLexeme();
            }
        );

        return '|' . $values->join('|') . '| ' . $this->getTokenCount();
    }

    /**
     * Scan a lexeme - a sequence of chars satisfying a check.
     *
     * @param  callable $check
     * @param  string   $lexeme
     * @return string
     */
    private function scan(callable $check, string $lexeme = '') : string
    {
        if (!$this->source->valid()) {
            return $lexeme;
        }

        $char = $this->source->current();

        if (!$check($char)) {
            return $lexeme;
        }

        $lexeme .= $char;
        $this->source->next();

        return $this->scan($check, $lexeme);
    }
}
