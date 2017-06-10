<?php
namespace Snout;

use \Snout\Exceptions\LexerException;
use \Snout\Token;
use \Snout\Utilities\StringIterator;

/**
 * Lexer.
 */
class Lexer
{
    /**
     * @const DELIMITER Lexing delimiter. URL paths are delimited with '/'.
     */
    const DELIMITER = '/';

    /**
     * @var StringIterator Source.
     */
    private $source;

    /**
     * @var string Current token.
     */
    private $token;

    /**
     * @var bool Payload flag.
     */
    private $has_payload;

    /**
     * @var string Current payload.
     */
    private $payload;

    /**
     * @var int Count.
     */
    private $count;

    /**
     * @param string $path Path for lexing.
     */
    public function __construct(string $path)
    {
        $this->source = new StringIterator($path);
        $this->count = 0;
        $this->has_payload = false;

        // If the path has a leading delimiter move past it.
        if ($this->source->valid() && $this->source->current() === self::DELIMITER) {
            $this->source->next();
        }

        // Scan first token.
        $this->next();
    }

    /**
     * @return string Current token.
     */
    public function getToken() : string
    {
        return $this->token;
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

        return $this->payload;
    }

    /**
     * @return int Count.
     */
    public function getCount() : int
    {
        return $this->count;
    }

    /**
     * Scan the next token.
     *
     * @throws \Snout\Exceptions\LexerException On encountering a disallowed char.
     *
     * @return void
     */
    public function next()
    {
        $this->has_payload = false;
        $this->count++;
        $token = null;
        $payload = '';

        if (!$this->source->valid()) {
            $this->token = Token::END;

            return;
        }

        // Scan by char to the next token.
        do {
            $char = $this->source->current();

            if ($char === self::DELIMITER) {
                // Move past the delimiter.
                $this->source->next();

                break;
            }

            switch ($char) {
                case ' ':
                case "\n":
                case "\t":
                case "\r":
                    // TODO configurable disallowed chars?
                    throw new LexerException('Whitespace in path.');
                    break;

                case '0':
                case '1':
                case '2':
                case '3':
                case '4':
                case '5':
                case '6':
                case '7':
                case '8':
                case '9':
                    // Numeric. Type is integer if it is not already a string.
                    if ($token === null) {
                        $token = Token::INTEGER;
                    }

                    break;

                default:
                    // Not numeric - always a string.
                    $token = Token::STRING;
                    break;
            }

            $payload .= $char;

            $this->source->next();
        } while ($this->source->valid());

        $this->token = $token;
        $this->has_payload = !empty($payload);

        if ($this->has_payload) {
            if ($token === Token::INTEGER) {
                $token = (int) $token;
            }

            $this->payload = $payload;
        }
    }
}
