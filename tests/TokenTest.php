<?php
namespace Snout\Tests;

use PHPUnit\Framework\TestCase;
use Snout\Exceptions\ParserException;
use Snout\Token;

class TokenTest extends TestCase
{
    public function test() : void
    {
        $token = new Token(Token::typeConstant('ALPHA'), 'foo', 'foo');
        $this->assertEquals(Token::ALPHA, $token->getType());
        $this->assertEquals(Token::ALPHA, $token->getType());
        $this->assertTrue($token->hasValue());
        $this->assertEquals('foo', $token->getValue());
    }

    public function testNoValueException() : void
    {
        $this->expectException(ParserException::class);
        $this->expectExceptionMessage('Token has no value.');

        $token = new Token('/', Token::typeConstant('FORWARD_SLASH'));
        $this->assertFalse($token->hasValue());
        $token->getValue();
    }
}
