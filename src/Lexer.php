<?php
namespace Snout;

use \Ds\Vector;
use \Snout\Exceptions\LexerException;
use \Snout\Utilities\StringIterator;
use \Snout\Token;

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
     * @var Vector $payloads
     */
    private $payloads;

    /**
     * @var bool $has_payload Payload flag.
     */
    private $has_payload;

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
        $this->payloads = new Vector();
        $this->has_payload = false;
        $this->column = 1;

        // Scan first token.
        $this->next();
    }

    /**
     * @return string Current token.
     */
    public function getToken() : string
    {
        return $this->tokens->last();
    }

    /**
     * @return bool Payload flag.
     */
    public function hasPayload() : bool
    {
        return $this->has_payload;
    }

    /**
     * @return string Current payload.
     * @throws LexerException If there is no current payload.
     */
    public function getPayload() : string
    {
        if (!$this->has_payload) {
            throw new LexerException('No current payload.');
        }

        return $this->payloads->last();
    }

    /**
     * @return int Token count.
     */
    public function getTokenCount() : int
    {
        return $this->tokens->count();
    }

    /**
     * @return int Payload count.
     */
    public function getPayloadCount() : int
    {
        return $this->payloads->count();
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
     * @throws LexerException On unexpected character.
     * @return void
     */
    public function next() : void
    {
        if (!$this->source->valid()) {
            $this->column = $this->source->key();
            $this->setResult(Token::END);

            return;
        }

        $this->column = $this->source->key() + 1;

        $payload = $this->scan(
            function ($char) {
                return ctype_digit($char);
            }
        );

        if ($payload !== '') {
            $this->setResult(Token::DIGIT, (int) $payload);

            return;
        }

        $payload = $this->scan(
            $alpha_check = function ($char) {
                return ctype_alpha($char);
            }
        );

        if ($payload !== '') {
            $this->setResult(Token::ALPHA, $payload);

            return;
        }

        $char = $this->source->current();
        $this->source->next();

        switch ($char) {
            case '/':
                $this->setResult(Token::FORWARD_SLASH);
                return;

            case '_':
                $this->setResult(Token::UNDERSCORE);
                return;

            case '-':
                $this->setResult(Token::HYPHEN);
                return;

            case ':':
                $this->setResult(Token::COLON);
                return;

            case '{':
                $this->setResult(Token::OPEN_BRACE);
                return;

            case '}':
                $this->setResult(Token::CLOSE_BRACE);
                return;

            case '\\':
                $this->setResult(Token::BACK_SLASH);
                return;

            case ' ':
                $this->setResult(Token::SPACE);
                return;

            case "\t":
                $this->setResult(Token::TAB);
                return;

            case "\n":
                $this->setResult(Token::NEW_LINE);
                return;

            case "\r":
                $this->setResult(Token::CARRIAGE_RETURN);
                return;

            default:
                throw new LexerException(
                    "Unexpected character: '{$char}'. At {$this->getCharCount()}."
                );
        }
    }

    /**
     * Scan a payload of characters satisfying a check.
     *
     * @param callable $check   Closure to check chars while scanning.
     * @param string   $payload Scanned payload.
     * @return string The scanned payload.
     */
    private function scan(callable $check, string $payload = '') : string
    {
        if (!$this->source->valid()) {
            return $payload;
        }

        $char = $this->source->current();

        if ($check($char)) {
            $payload .= $char;
            $this->source->next();

            return $this->scan($check, $payload);
        }

        return $payload;
    }

    /**
     * Set scanned token and payload.
     *
     * @param string  $token   The token to set.
     * @param ?string $payload The payload to set.
     * @return void
     */
    private function setResult(string $token, ?string $payload = null) : void
    {
        $this->tokens->push($token);

        if ($payload == null) {
            $this->has_payload = false;

            return;
        }

        $this->payloads->push($payload);
        $this->has_payload = true;
    }
}