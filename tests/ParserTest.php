<?php
namespace Snout\Tests;

use PHPUnit\Framework\TestCase;
use InvalidArgumentException;
use Ds\Map;
use Snout\Exceptions\ConfigurationException;
use Snout\Exceptions\ParserException;
use Snout\Token;
use Snout\Lexer;
use Snout\Parser;

class ParserTest extends TestCase
{
    public static function configAndParserProvider() : array
    {
        $config = \Snout\json_decode_file(__DIR__ . '/configs/test.json');
        $parser = new Parser(
            $config->get('parser'),
            new Lexer("foo1234/_-.:{}[]\\")
        );

        return [[$config, $parser]];
    }

    /**
     * @dataProvider configAndParserProvider
     */
    public function testParser(Map $config, Parser $parser) : void
    {
        $this->assertFalse($parser->isComplete());
        $this->assertEquals(0, $parser->getIndex());
        $this->assertEquals(Token::ALPHA, $parser->getToken()->getType());
        $this->assertTrue($parser->getToken()->hasValue());
        $this->assertEquals('foo', $parser->getToken()->getValue());

        $this->assertNull($parser->accept(Token::ALPHA));
        $this->assertFalse($parser->isComplete());
        $this->assertEquals(1, $parser->getIndex());
        $this->assertEquals(Token::DIGIT, $parser->getToken()->getType());
        $this->assertTrue($parser->getToken()->hasValue());
        $this->assertEquals(1234, $parser->getToken()->getValue());

        $this->assertNull($parser->accept(Token::DIGIT));
        $this->assertFalse($parser->isComplete());
        $this->assertEquals(2, $parser->getIndex());
        $this->assertEquals(Token::FORWARD_SLASH, $parser->getToken()->getType());
        $this->assertFalse($parser->getToken()->hasValue());

        $this->assertNull($parser->accept(Token::FORWARD_SLASH));
        $this->assertFalse($parser->isComplete());
        $this->assertEquals(3, $parser->getIndex());
        $this->assertEquals(Token::UNDERSCORE, $parser->getToken()->getType());
        $this->assertFalse($parser->getToken()->hasValue());

        $this->assertNull($parser->accept(Token::UNDERSCORE));
        $this->assertFalse($parser->isComplete());
        $this->assertEquals(4, $parser->getIndex());
        $this->assertEquals(Token::HYPHEN, $parser->getToken()->getType());
        $this->assertFalse($parser->getToken()->hasValue());

        $this->assertNull($parser->accept(Token::HYPHEN));
        $this->assertFalse($parser->isComplete());
        $this->assertEquals(5, $parser->getIndex());
        $this->assertEquals(Token::PERIOD, $parser->getToken()->getType());
        $this->assertFalse($parser->getToken()->hasValue());

        $this->assertNull($parser->accept(Token::PERIOD));
        $this->assertFalse($parser->isComplete());
        $this->assertEquals(6, $parser->getIndex());
        $this->assertEquals(Token::COLON, $parser->getToken()->getType());
        $this->assertFalse($parser->getToken()->hasValue());

        $this->assertNull($parser->accept(Token::COLON));
        $this->assertFalse($parser->isComplete());
        $this->assertEquals(7, $parser->getIndex());
        $this->assertEquals(Token::OPEN_BRACE, $parser->getToken()->getType());
        $this->assertFalse($parser->getToken()->hasValue());

        $this->assertNull($parser->accept(Token::OPEN_BRACE));
        $this->assertFalse($parser->isComplete());
        $this->assertEquals(8, $parser->getIndex());
        $this->assertEquals(Token::CLOSE_BRACE, $parser->getToken()->getType());
        $this->assertFalse($parser->getToken()->hasValue());

        $this->assertNull($parser->accept(Token::CLOSE_BRACE));
        $this->assertFalse($parser->isComplete());
        $this->assertEquals(9, $parser->getIndex());
        $this->assertEquals(Token::OPEN_BRACKET, $parser->getToken()->getType());
        $this->assertFalse($parser->getToken()->hasValue());

        $this->assertNull($parser->accept(Token::OPEN_BRACKET));

        $this->assertFalse($parser->isComplete());
        $this->assertEquals(10, $parser->getIndex());
        $this->assertEquals(Token::CLOSE_BRACKET, $parser->getToken()->getType());
        $this->assertFalse($parser->getToken()->hasValue());

        $this->assertNull($parser->accept(Token::CLOSE_BRACKET));
        $this->assertFalse($parser->isComplete());
        $this->assertEquals(11, $parser->getIndex());
        $this->assertEquals(Token::BACK_SLASH, $parser->getToken()->getType());
        $this->assertFalse($parser->getToken()->hasValue());

        $this->assertNull($parser->accept(Token::BACK_SLASH));
        $this->assertTrue($parser->isComplete());
        $this->assertEquals(12, $parser->getIndex());
        $this->assertEquals(Token::END, $parser->getToken()->getType());
        $this->assertFalse($parser->getToken()->hasValue());
    }

    /**
     * @dataProvider configAndParserProvider
     */
    public function testOptionalAccept(Map $config, Parser $parser) : void
    {
        $parser = new Parser($config->get('parser'), new Lexer('.foo'));

        $this->assertEquals(Token::PERIOD, $parser->getToken()->getType());

        $this->assertNull($parser->optional(Token::ALPHA));
        $this->assertEquals(Token::PERIOD, $parser->getToken()->getType());

        $this->assertNull($parser->optional(Token::PERIOD));
        $this->assertEquals(Token::ALPHA, $parser->getToken()->getType());

        $this->assertNull($parser->optional(Token::ALPHA, 'bar'));
        $this->assertEquals(Token::ALPHA, $parser->getToken()->getType());
        $this->assertEquals('foo', $parser->getToken()->getValue());

        $this->assertNull($parser->optional(Token::ALPHA, 'foo'));
        $this->assertTrue($parser->isComplete());
    }

    /**
     * @dataProvider configAndParserProvider
     */
    public function testJump(Map $config, Parser $parser) : void
    {
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
        $this->assertNull($parser->accept(Token::OPEN_BRACKET));
        $this->assertNull($parser->accept(Token::CLOSE_BRACKET));
        $this->assertNull($parser->accept(Token::BACK_SLASH));
        $this->assertNull($parser->accept(Token::END));
        $this->assertTrue($parser->isComplete());
    }

    public function testInvalidConfig() : void
    {
        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage(
            "Invalid configuration. Missing keys: 'invalid'"
        );

        $parser = new Parser(new Map(), new Lexer(' '));
    }

    /**
     * @dataProvider configAndParserProvider
     */
    public function testInvalidAcceptArguments(Map $config, Parser $parser) : void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'First argument must be a string, an instance of \Snout\Token or null.'
        );

        $parser = new Parser(
            $config->get('parser'),
            new Lexer('/foo')
        );

        $parser->accept(12);
    }

    /**
     * @dataProvider configAndParserProvider
     */
    public function testInvalidToken(Map $config, Parser $parser) : void
    {
        $this->expectException(ParserException::class);
        $this->expectExceptionMessage("Invalid token type 'SPACE'. At char 1.");

        $parser = new Parser(
            $config->get('parser'),
            new Lexer(' ')
        );

        $parser->accept();
    }

    /**
     * @dataProvider configAndParserProvider
     */
    public function testUnexpectedToken(Map $config, Parser $parser) : void
    {
        $this->expectException(ParserException::class);
        $this->expectExceptionMessage(
            "Unexpected token type 'ALPHA'. Expecting token type 'DIGIT'. "
            . "At char 1."
        );

        $parser = new Parser($config->get('parser'), new Lexer('foo'));
        $parser->accept(Token::DIGIT);
    }

    /**
     * @dataProvider configAndParserProvider
     */
    public function testUnexpectedPayload(Map $config, Parser $parser) : void
    {
        $this->expectException(ParserException::class);
        $this->expectExceptionMessage(
            "Unexpected 'foo'. Expecting 'bar'. At char 1."
        );

        $parser = new Parser($config->get('parser'), new Lexer('foo'));
        $parser->accept(Token::ALPHA, 'bar');
    }

    /**
     * @dataProvider configAndParserProvider
     */
    public function testExpectedPayload(Map $config, Parser $parser) : void
    {
        $this->expectException(ParserException::class);
        $this->expectExceptionMessage("Expecting 'foo'. At char 1.");

        $parser = new Parser($config->get('parser'), new Lexer('/'));
        $parser->accept(Token::FORWARD_SLASH, 'foo');
    }

    /**
     * @dataProvider configAndParserProvider
     */
    public function testInvalidJump(Map $config, Parser $parser) : void
    {
        $this->expectException('OutOfRangeException');
        $this->expectExceptionMessage(
            "Index out of range: 3, expected 0 <= x <= 1"
        );

        $parser = new Parser($config->get('parser'), new Lexer(' '));
        $parser->jump(3);
    }

    /**
     * @dataProvider configAndParserProvider
     */
    public function testDebug(Map $config, Parser $parser) : void
    {
        $this->assertNull($parser->accept(Token::ALPHA));
        $this->assertNull($parser->accept(Token::DIGIT));
        $this->assertNull($parser->accept(Token::FORWARD_SLASH));
        $this->assertNull($parser->accept(Token::UNDERSCORE));

        $this->assertEquals('|foo|1234|/|_|-| 5', $parser->debug());
    }
}
