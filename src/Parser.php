<?php
namespace Snout;

use \Snout\Lexer;

/**
 * Parser.
 */
class Parser
{
    /**
     * @var Lexer Lexer.
     */
    private $lexer;

    /**
     * @param Lexer  Lexer.
     * @param string HTTP Method.
     */
    public function __construct(Lexer $lexer, string $method)
    {
        $this->lexer = $lexer;
        $this->method = $method;
    }
}
