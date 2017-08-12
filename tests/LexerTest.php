<?php
namespace Snout\Tests;

use PHPUnit\Framework\TestCase;
use Snout\Exceptions\ParserException;
use Snout\Lexer;
use Snout\Token;

class LexerTest extends TestCase
{
    public function test() : void
    {
        $lexer = new Lexer("foo1234/_-.:{}\\ \t\n\r");

        $this->assertEquals(1, $lexer->getColumn());
        $this->assertEquals(1, $lexer->getTokenCount());
        $this->assertEquals(3, $lexer->getCharCount());
        $this->assertEquals(Token::ALPHA, $lexer->getTokenType());
        $this->assertTrue($lexer->tokenHasValue());
        $this->assertEquals('foo', $lexer->getTokenValue());

        $lexer->next();

        $this->assertEquals(4, $lexer->getColumn());
        $this->assertEquals(2, $lexer->getTokenCount());
        $this->assertEquals(7, $lexer->getCharCount());
        $this->assertEquals(Token::DIGIT, $lexer->getTokenType());
        $this->assertTrue($lexer->tokenHasValue());
        $this->assertEquals(1234, $lexer->getTokenValue());

        $lexer->next();

        $this->assertEquals(8, $lexer->getColumn());
        $this->assertEquals(3, $lexer->getTokenCount());
        $this->assertEquals(8, $lexer->getCharCount());
        $this->assertEquals(Token::FORWARD_SLASH, $lexer->getTokenType());
        $this->assertFalse($lexer->tokenHasValue());

        $lexer->next();

        $this->assertEquals(9, $lexer->getColumn());
        $this->assertEquals(4, $lexer->getTokenCount());
        $this->assertEquals(9, $lexer->getCharCount());
        $this->assertEquals(Token::UNDERSCORE, $lexer->getTokenType());
        $this->assertFalse($lexer->tokenHasValue());

        $lexer->next();

        $this->assertEquals(10, $lexer->getColumn());
        $this->assertEquals(5, $lexer->getTokenCount());
        $this->assertEquals(10, $lexer->getCharCount());
        $this->assertEquals(Token::HYPHEN, $lexer->getTokenType());
        $this->assertFalse($lexer->tokenHasValue());

        $lexer->next();

        $this->assertEquals(11, $lexer->getColumn());
        $this->assertEquals(6, $lexer->getTokenCount());
        $this->assertEquals(11, $lexer->getCharCount());
        $this->assertEquals(Token::PERIOD, $lexer->getTokenType());
        $this->assertFalse($lexer->tokenHasValue());

        $lexer->next();

        $this->assertEquals(12, $lexer->getColumn());
        $this->assertEquals(7, $lexer->getTokenCount());
        $this->assertEquals(12, $lexer->getCharCount());
        $this->assertFalse($lexer->tokenHasValue());

        $lexer->next();

        $this->assertEquals(13, $lexer->getColumn());
        $this->assertEquals(8, $lexer->getTokenCount());
        $this->assertEquals(13, $lexer->getCharCount());
        $this->assertEquals(Token::OPEN_BRACE, $lexer->getTokenType());
        $this->assertFalse($lexer->tokenHasValue());

        $lexer->next();

        $this->assertEquals(14, $lexer->getColumn());
        $this->assertEquals(9, $lexer->getTokenCount());
        $this->assertEquals(14, $lexer->getCharCount());
        $this->assertEquals(Token::CLOSE_BRACE, $lexer->getTokenType());
        $this->assertFalse($lexer->tokenHasValue());

        $lexer->next();

        $this->assertEquals(15, $lexer->getColumn());
        $this->assertEquals(10, $lexer->getTokenCount());
        $this->assertEquals(15, $lexer->getCharCount());
        $this->assertEquals(Token::BACK_SLASH, $lexer->getTokenType());
        $this->assertFalse($lexer->tokenHasValue());

        $lexer->next();

        $this->assertEquals(16, $lexer->getColumn());
        $this->assertEquals(11, $lexer->getTokenCount());
        $this->assertEquals(16, $lexer->getCharCount());
        $this->assertEquals(Token::SPACE, $lexer->getTokenType());
        $this->assertFalse($lexer->tokenHasValue());

        $lexer->next();

        $this->assertEquals(17, $lexer->getColumn());
        $this->assertEquals(12, $lexer->getTokenCount());
        $this->assertEquals(17, $lexer->getCharCount());
        $this->assertEquals(Token::TAB, $lexer->getTokenType());
        $this->assertFalse($lexer->tokenHasValue());

        $lexer->next();

        $this->assertEquals(18, $lexer->getColumn());
        $this->assertEquals(13, $lexer->getTokenCount());
        $this->assertEquals(18, $lexer->getCharCount());
        $this->assertEquals(Token::NEW_LINE, $lexer->getTokenType());
        $this->assertFalse($lexer->tokenHasValue());

        $lexer->next();

        $this->assertEquals(19, $lexer->getColumn());
        $this->assertEquals(14, $lexer->getTokenCount());
        $this->assertEquals(19, $lexer->getCharCount());
        $this->assertEquals(Token::CARRIAGE_RETURN, $lexer->getTokenType());
        $this->assertFalse($lexer->tokenHasValue());

        $lexer->next();

        $this->assertEquals(19, $lexer->getColumn());
        $this->assertEquals(15, $lexer->getTokenCount());
        $this->assertEquals(19, $lexer->getCharCount());
        $this->assertEquals(Token::END, $lexer->getTokenType());
        $this->assertFalse($lexer->tokenHasValue());
    }

    public function testNoValueException() : void
    {
        $this->expectException(ParserException::class);
        $this->expectExceptionMessage('Token has no value.');

        $lexer = new Lexer('/');
        $lexer->getTokenValue();
    }

    public function testUnexpectedCharException() : void
    {
        $this->expectException(ParserException::class);
        $this->expectExceptionMessage("Unexpected character: '''. At 1.");

        $lexer = new Lexer("'");
    }
}
