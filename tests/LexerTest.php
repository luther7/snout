<?php
namespace Snout\Tests;

use \PHPUnit\Framework\TestCase;
use Snout\Lexer;
use Snout\Token;

class LexerTest extends TestCase
{
    public function testLeadingSlash()
    {
        $lexer = new Lexer('/foo');

        $this->assertEquals(1, $lexer->getCount());
        $this->assertEquals(Token::STRING, $lexer->getToken());
        $this->assertTrue($lexer->hasPayload());
        $this->assertEquals('foo', $lexer->getPayload());

        $lexer->next();

        $this->assertEquals(2, $lexer->getCount());
        $this->assertEquals(Token::END, $lexer->getToken());
        $this->assertFalse($lexer->hasPayload());
    }

    public function testNoLeadingSlash()
    {
        $lexer = new Lexer('foo');

        $this->assertEquals(1, $lexer->getCount());
        $this->assertEquals(Token::STRING, $lexer->getToken());
        $this->assertTrue($lexer->hasPayload());
        $this->assertEquals('foo', $lexer->getPayload());

        $lexer->next();

        $this->assertEquals(2, $lexer->getCount());
        $this->assertEquals(Token::END, $lexer->getToken());
        $this->assertFalse($lexer->hasPayload());
    }

    public function testLexer()
    {
        $lexer = new Lexer('/foo/bar/1234/baz');

        while ($lexer->getToken() !== Token::END) {
            switch ($lexer->getCount()) {
                case 1:
                    $this->assertEquals(Token::STRING, $lexer->getToken());
                    $this->assertTrue($lexer->hasPayload());
                    $this->assertEquals('foo', $lexer->getPayload());
                    break;

                case 2:
                    $this->assertEquals(Token::STRING, $lexer->getToken());
                    $this->assertTrue($lexer->hasPayload());
                    $this->assertEquals('bar', $lexer->getPayload());
                    break;

                case 3:
                    $this->assertEquals(Token::INTEGER, $lexer->getToken());
                    $this->assertTrue($lexer->hasPayload());
                    $this->assertEquals(1234, $lexer->getPayload());
                    break;

                case 4:
                    $this->assertEquals(Token::STRING, $lexer->getToken());
                    $this->assertTrue($lexer->hasPayload());
                    $this->assertEquals('baz', $lexer->getPayload());
                    break;

                case 5:
                    $this->assertEquals(Token::END, $lexer->getToken());
                    $this->assertFalse($lexer->hasPayload());
                    break;
            }

            $lexer->next();
        }
    }
}