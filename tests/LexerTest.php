<?php
namespace Snout\Tests;

use \PHPUnit\Framework\TestCase;
use Snout\Lexer;
use Snout\Token;

class LexerTest extends TestCase
{
    public function testLexer()
    {
        $lexer = new Lexer("foo1234/_-\\ \t\n\r");

        $this->assertEquals(1, $lexer->getTokenCount());
        $this->assertEquals(3, $lexer->getCharCount());
        $this->assertEquals(Token::ALPHA, $lexer->getToken());
        $this->assertTrue($lexer->hasPayload());
        $this->assertEquals('foo', $lexer->getPayload());

        $lexer->next();

        $this->assertEquals(2, $lexer->getTokenCount());
        $this->assertEquals(7, $lexer->getCharCount());
        $this->assertEquals(Token::DIGIT, $lexer->getToken());
        $this->assertTrue($lexer->hasPayload());
        $this->assertEquals(1234, $lexer->getPayload());

        $lexer->next();

        $this->assertEquals(3, $lexer->getTokenCount());
        $this->assertEquals(8, $lexer->getCharCount());
        $this->assertEquals(Token::FORWARD_SLASH, $lexer->getToken());
        $this->assertFalse($lexer->hasPayload());

        $lexer->next();

        $this->assertEquals(4, $lexer->getTokenCount());
        $this->assertEquals(9, $lexer->getCharCount());
        $this->assertEquals(Token::UNDERSCORE, $lexer->getToken());
        $this->assertFalse($lexer->hasPayload());

        $lexer->next();

        $this->assertEquals(5, $lexer->getTokenCount());
        $this->assertEquals(10, $lexer->getCharCount());
        $this->assertEquals(Token::HYPHEN, $lexer->getToken());
        $this->assertFalse($lexer->hasPayload());

        $lexer->next();

        $this->assertEquals(6, $lexer->getTokenCount());
        $this->assertEquals(11, $lexer->getCharCount());
        $this->assertEquals(Token::BACK_SLASH, $lexer->getToken());
        $this->assertFalse($lexer->hasPayload());

        $lexer->next();

        $this->assertEquals(7, $lexer->getTokenCount());
        $this->assertEquals(12, $lexer->getCharCount());
        $this->assertEquals(Token::SPACE, $lexer->getToken());
        $this->assertFalse($lexer->hasPayload());

        $lexer->next();

        $this->assertEquals(8, $lexer->getTokenCount());
        $this->assertEquals(13, $lexer->getCharCount());
        $this->assertEquals(Token::TAB, $lexer->getToken());
        $this->assertFalse($lexer->hasPayload());

        $lexer->next();

        $this->assertEquals(9, $lexer->getTokenCount());
        $this->assertEquals(14, $lexer->getCharCount());
        $this->assertEquals(Token::NEW_LINE, $lexer->getToken());
        $this->assertFalse($lexer->hasPayload());

        $lexer->next();

        $this->assertEquals(10, $lexer->getTokenCount());
        $this->assertEquals(15, $lexer->getCharCount());
        $this->assertEquals(Token::CARRIAGE_RETURN, $lexer->getToken());
        $this->assertFalse($lexer->hasPayload());

        $lexer->next();

        $this->assertEquals(11, $lexer->getTokenCount());
        $this->assertEquals(15, $lexer->getCharCount());
        $this->assertEquals(Token::END, $lexer->getToken());
        $this->assertFalse($lexer->hasPayload());
    }
}