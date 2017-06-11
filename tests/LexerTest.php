<?php
namespace Snout\Tests;

use \PHPUnit\Framework\TestCase;
use Snout\Lexer;
use Snout\Token;

class LexerTest extends TestCase
{
    public function testLexer()
    {
        $lexer = new Lexer('/foo_\989bar-');

        $this->assertEquals(1, $lexer->getTokenCount());
        $this->assertEquals(1, $lexer->getCharCount());
        $this->assertEquals(Token::FORWARD_SLASH, $lexer->getToken());
        $this->assertFalse($lexer->hasPayload());

        $lexer->next();

        $this->assertEquals(2, $lexer->getTokenCount());
        $this->assertEquals(4, $lexer->getCharCount());
        $this->assertEquals(Token::ALPHA, $lexer->getToken());
        $this->assertTrue($lexer->hasPayload());
        $this->assertEquals('foo', $lexer->getPayload());

        $lexer->next();

        $this->assertEquals(3, $lexer->getTokenCount());
        $this->assertEquals(5, $lexer->getCharCount());
        $this->assertEquals(Token::UNDERSCORE, $lexer->getToken());
        $this->assertFalse($lexer->hasPayload());

        $lexer->next();

        $this->assertEquals(4, $lexer->getTokenCount());
        $this->assertEquals(6, $lexer->getCharCount());
        $this->assertEquals(Token::BACK_SLASH, $lexer->getToken());
        $this->assertFalse($lexer->hasPayload());

        $lexer->next();

        $this->assertEquals(5, $lexer->getTokenCount());
        $this->assertEquals(9, $lexer->getCharCount());
        $this->assertEquals(Token::DIGIT, $lexer->getToken());
        $this->assertTrue($lexer->hasPayload());
        $this->assertEquals(989, $lexer->getPayload());

        $lexer->next();

        $this->assertEquals(6, $lexer->getTokenCount());
        $this->assertEquals(12, $lexer->getCharCount());
        $this->assertEquals(Token::ALPHA, $lexer->getToken());
        $this->assertTrue($lexer->hasPayload());
        $this->assertEquals('bar', $lexer->getPayload());

        $lexer->next();

        $this->assertEquals(7, $lexer->getTokenCount());
        $this->assertEquals(13, $lexer->getCharCount());
        $this->assertEquals(Token::HYPHEN, $lexer->getToken());
        $this->assertFalse($lexer->hasPayload());

        $lexer->next();

        $this->assertEquals(8, $lexer->getTokenCount());
        $this->assertEquals(13, $lexer->getCharCount());
        $this->assertEquals(Token::END, $lexer->getToken());
        $this->assertFalse($lexer->hasPayload());
    }
}