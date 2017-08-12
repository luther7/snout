<?php
namespace Snout\Tests;

use PHPUnit\Framework\TestCase;
use Ds\Map;
use Snout\Exceptions\ConfigurationException;
use Snout\Exceptions\ParserException;
use Snout\Token;
use Snout\Lexer;
use Snout\Parser;

class ParserTest extends TestCase
{
    public function test() : void
    {
        $config = \Snout\json_decode_file(
            __DIR__ . '/configs/test.json'
        );

        $parser = new Parser(
            $config->get('parser'),
            new Lexer("foo1234/_-.:{}\\")
        );

        $this->assertFalse($parser->isEnd());

        $this->assertEquals(0, $parser->getIndex());
        $this->assertEquals(Token::ALPHA, $parser->getTokenType());
        $this->assertTrue($parser->tokenHasValue());
        $this->assertEquals('foo', $parser->getTokenValue());

        $this->assertNull($parser->accept(Token::ALPHA));

        $this->assertEquals(1, $parser->getIndex());
        $this->assertEquals(Token::DIGIT, $parser->getTokenType());
        $this->assertTrue($parser->tokenHasValue());
        $this->assertEquals(1234, $parser->getTokenValue());

        $this->assertNull($parser->accept(Token::DIGIT));

        $this->assertEquals(2, $parser->getIndex());
        $this->assertEquals(Token::FORWARD_SLASH, $parser->getTokenType());
        $this->assertFalse($parser->tokenHasValue());

        $this->assertNull($parser->accept(Token::FORWARD_SLASH));

        $this->assertEquals(3, $parser->getIndex());
        $this->assertEquals(Token::UNDERSCORE, $parser->getTokenType());
        $this->assertFalse($parser->tokenHasValue());

        $this->assertNull($parser->accept(Token::UNDERSCORE));

        $this->assertEquals(4, $parser->getIndex());
        $this->assertEquals(Token::HYPHEN, $parser->getTokenType());
        $this->assertFalse($parser->tokenHasValue());

        $this->assertNull($parser->accept(Token::HYPHEN));

        $this->assertEquals(5, $parser->getIndex());
        $this->assertEquals(Token::PERIOD, $parser->getTokenType());
        $this->assertFalse($parser->tokenHasValue());

        $this->assertNull($parser->accept(Token::PERIOD));

        $this->assertEquals(6, $parser->getIndex());
        $this->assertEquals(Token::COLON, $parser->getTokenType());
        $this->assertFalse($parser->tokenHasValue());

        $this->assertNull($parser->accept(Token::COLON));

        $this->assertEquals(7, $parser->getIndex());
        $this->assertEquals(Token::OPEN_BRACE, $parser->getTokenType());
        $this->assertFalse($parser->tokenHasValue());

        $this->assertNull($parser->accept(Token::OPEN_BRACE));

        $this->assertEquals(8, $parser->getIndex());
        $this->assertEquals(Token::CLOSE_BRACE, $parser->getTokenType());
        $this->assertFalse($parser->tokenHasValue());

        $this->assertNull($parser->accept(Token::CLOSE_BRACE));

        $this->assertEquals(9, $parser->getIndex());
        $this->assertEquals(Token::BACK_SLASH, $parser->getTokenType());
        $this->assertFalse($parser->tokenHasValue());

        $this->assertNull($parser->accept(Token::BACK_SLASH));

        $this->assertEquals(10, $parser->getIndex());
        $this->assertEquals(Token::END, $parser->getTokenType());
        $this->assertFalse($parser->tokenHasValue());

        $this->assertTrue($parser->isEnd());
    }

    public function testOptionalAccept() : void
    {
        $config = \Snout\json_decode_file(
            __DIR__ . '/configs/test.json',
            true
        );

        $parser = new Parser(
            $config->get('parser'),
            new Lexer('.foo')
        );

        $this->assertEquals(Token::PERIOD, $parser->getTokenType());
        $this->assertNull($parser->optional(Token::ALPHA));
        $this->assertEquals(Token::PERIOD, $parser->getTokenType());
        $this->assertNull($parser->optional(Token::PERIOD));
        $this->assertEquals(Token::ALPHA, $parser->getTokenType());
        $this->assertNull($parser->optional(Token::ALPHA, 'bar'));
        $this->assertEquals(Token::ALPHA, $parser->getTokenType());
        $this->assertEquals('foo', $parser->getTokenValue());
        $this->assertNull($parser->accept(Token::ALPHA, 'foo'));
        $this->assertTrue($parser->isEnd());
    }

    public function testJump() : void
    {
        $config = \Snout\json_decode_file(
            __DIR__ . '/configs/test.json'
        );

        $parser = new Parser(
            $config->get('parser'),
            new Lexer("foo1234/_-.:{}\\")
        );

        $this->assertNull($parser->accept(Token::ALPHA));
        $this->assertNull($parser->accept(Token::DIGIT));
        $this->assertNull($parser->accept(Token::FORWARD_SLASH));
        $this->assertNull($parser->accept(Token::UNDERSCORE));
        $this->assertNull($parser->accept(Token::HYPHEN));
        $this->assertNull($parser->accept(Token::PERIOD));
        $this->assertNull($parser->accept(Token::COLON));

        $parser->jump(4);
        $this->assertNull($parser->accept(Token::HYPHEN));
        $this->assertNull($parser->accept(Token::PERIOD));
        $this->assertNull($parser->accept(Token::COLON));
        $this->assertNull($parser->accept(Token::OPEN_BRACE));
        $this->assertNull($parser->accept(Token::CLOSE_BRACE));
        $this->assertNull($parser->accept(Token::BACK_SLASH));
        $this->assertNull($parser->accept(Token::END));
        $this->assertTrue($parser->isEnd());
    }

    public function testInvalidConfig() : void
    {
        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage(
            "Invalid configuration. Missing keys: 'invalid'"
        );

        $parser = new Parser(
            new Map(),
            new Lexer(' ')
        );
    }

    public function testInvalidToken() : void
    {
        $this->expectException(ParserException::class);
        $this->expectExceptionMessage("Invalid token type 'SPACE'. At char 1.");

        $config = \Snout\json_decode_file(
            __DIR__ . '/configs/test.json',
            true
        );

        $parser = new Parser(
            $config->get('parser'),
            new Lexer(' ')
        );

        $parser->accept();
    }

    public function testUnexpectedToken() : void
    {
        $this->expectException(ParserException::class);
        $this->expectExceptionMessage(
            "Unexpected token type 'ALPHA'. Expecting token type 'DIGIT'. "
            . "At char 1."
        );

        $config = \Snout\json_decode_file(
            __DIR__ . '/configs/test.json',
            true
        );

        $parser = new Parser(
            $config->get('parser'),
            new Lexer('foo')
        );

        $parser->accept(Token::DIGIT);
    }

    public function testUnexpectedPayload() : void
    {
        $this->expectException(ParserException::class);
        $this->expectExceptionMessage(
            "Unexpected 'foo'. Expecting 'bar'. At char 1."
        );

        $config = \Snout\json_decode_file(
            __DIR__ . '/configs/test.json',
            true
        );

        $parser = new Parser(
            $config->get('parser'),
            new Lexer('foo')
        );

        $parser->accept(Token::ALPHA, 'bar');
    }

    public function testExpectedPayload() : void
    {
        $this->expectException(ParserException::class);
        $this->expectExceptionMessage(
            "Expecting 'foo'. At char 1."
        );

        $config = \Snout\json_decode_file(
            __DIR__ . '/configs/test.json',
            true
        );

        $parser = new Parser(
            $config->get('parser'),
            new Lexer('/')
        );

        $parser->accept(Token::FORWARD_SLASH, 'foo');
    }

    public function testInvalidJump() : void
    {
        $this->expectException('OutOfRangeException');
        $this->expectExceptionMessage(
            "Index out of range: 3, expected 0 <= x <= 1"
        );

        $config = \Snout\json_decode_file(
            __DIR__ . '/configs/test.json',
            true
        );

        $parser = new Parser(
            $config->get('parser'),
            new Lexer(' ')
        );

        $parser->jump(3);
    }
}
