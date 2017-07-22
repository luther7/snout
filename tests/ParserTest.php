<?php
namespace Snout\Tests;

use \PHPUnit\Framework\TestCase;
use Snout\Exceptions\ParserException;
use Snout\Token;
use Snout\Config;
use Snout\Lexer;
use Snout\Parser;

class ParserTest extends TestCase
{
    public function test() : void
    {
        $config = \Snout\json_decode_file(
            __DIR__ . '/configs/test.json'
        );

        $parser = new Parser($config, new Lexer("foo1234/_-\\"));
        $this->assertNull($parser->accept(Token::ALPHA));
        $this->assertNull($parser->accept(Token::DIGIT));
        $this->assertNull($parser->accept(Token::FORWARD_SLASH));
        $this->assertNull($parser->accept(Token::UNDERSCORE));
        $this->assertNull($parser->accept(Token::HYPHEN));
        $this->assertNull($parser->accept(Token::BACK_SLASH));
    }

    public function testUnacceptableToken() : void
    {
        $this->expectException(ParserException::class);
        $this->expectExceptionMessage(
            "Unexpected token 'ALPHA'. Expecting token 'DIGIT'. At char 1."
        );

        $config = \Snout\json_decode_file(
            __DIR__ . '/configs/test.json',
            true
        );

        $parser = new Parser($config, new Lexer('foo'));
        $parser->accept(Token::DIGIT);
    }

    public function testInvalidToken() : void
    {
        $this->expectException(ParserException::class);
        $this->expectExceptionMessage("Invalid token 'SPACE'. At char 1.");

        $config = \Snout\json_decode_file(
            __DIR__ . '/configs/test.json',
            true
        );

        $parser = new Parser($config, new Lexer(' '));
        $parser->accept();
    }
}
