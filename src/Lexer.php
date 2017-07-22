<?php
namespace Snout;

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
     * @var array $tokens
     */
    private $tokens;

    /**
     * @var bool $has_payload Payload flag.
     */
    private $has_payload;

    /**
     * @var array $payloads
     */
    private $payloads;

    /**
     * @var int $token_count
     */
    private $token_count;

    /**
     * @var int $payload_count
     */
    private $payload_count;

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
        $this->tokens = [];
        $this->has_payload = false;
        $this->payloads = [];
        $this->token_count = 0;
        $this->payload_count = 0;
        $this->column = 1;

        // Scan first token.
        $this->next();
    }

    /**
     * @return string Current token.
     */
    public function getToken() : string
    {
        return $this->tokens[$this->token_count - 1];
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
     *
     * @throws \Snout\Exceptions\LexerException If there is no current payload.
     */
    public function getPayload() : string
    {
        if (!$this->has_payload) {
            throw new LexerException('No current payload.');
        }

        return $this->payloads[$this->payload_count - 1];
    }

    /**
     * @return int Token count.
     */
    public function getTokenCount() : int
    {
        return $this->token_count;
    }

    /**
     * @return int Payload count.
     */
    public function getPayloadCount() : int
    {
        return $this->payload_count;
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
     * Scan the next token.
     *
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

        $digit_check = function ($char) {
            return ctype_digit($char);
        };

        if (($payload = $this->scan($digit_check)) !== '') {
            $this->setResult(Token::DIGIT, (int) $payload);

            return;
        }

        $alpha_check = function ($char) {
            return ctype_alpha($char);
        };

        if (($payload = $this->scan($alpha_check)) !== '') {
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
     * Scan a payload of chars satisfying a check.
     *
     * @param callable $check   Closure to check chars while scanning.
     * @param string   $payload Scanned payload.
     *
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
     * @param string      $token   The token to set.
     * @param ?string $payload The payload to set.
     *
     * @return void
     */
    private function setResult(string $token, ?string $payload = null)
    {
        $this->tokens[] = $token;
        $this->token_count++;

        if ($payload !== null) {
            $this->payloads[] = $payload;
            $this->has_payload = true;
            $this->payload_count++;
        } else {
            $this->has_payload = false;
        }
    }
}