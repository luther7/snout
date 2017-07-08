<?php
namespace Snout\Tests;

use \PHPUnit\Framework\TestCase;
use Snout\Token;
use Snout\Config;
use Snout\Lexer;
use Snout\Parser;
use Snout\Exceptions\ParserException;

class ParserTest extends TestCase
{
    public function test()
    {
        $config = \Snout\json_decode_file(
            __DIR__ . '/../src/default_config.json',
            true
        );

        $parser = new Parser(
            new Config($config),
            new Lexer("foo1234/_-\\")
        );

        $this->assertNull($parser->accept(Token::ALPHA));
        $this->assertNull($parser->accept(Token::DIGIT));
        $this->assertNull($parser->accept(Token::FORWARD_SLASH));
        $this->assertNull($parser->accept(Token::UNDERSCORE));
        $this->assertNull($parser->accept(Token::HYPHEN));
        $this->assertNull($parser->accept(Token::BACK_SLASH));
    }

    /**
     * @expectedException Snout\Exceptions\ParserException
     */
    public function testUnacceptableToken()
    {
        $config = \Snout\json_decode_file(
            __DIR__ . '/../src/default_config.json',
            true
        );

        $parser = new Parser(
            new Config($config),
            new Lexer('foo')
        );

        $parser->accept(Token::DIGIT);
    }

    /**
    * @expectedException Snout\Exceptions\ParserException
    */
    public function testInvalidToken()
    {
        $config = \Snout\json_decode_file(
            __DIR__ . '/../src/default_config.json',
            true
        );

        $parser = new Parser(
            new Config($config),
            new Lexer(' ')
        );

        $parser->accept(Token::SPACE);
    }
}