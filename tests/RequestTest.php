<?php
namespace Snout\Tests;

use PHPUnit\Framework\TestCase;
use InvalidArgumentException;
use Ds\Map;
use Snout\Lexer;
use Snout\Parser;
use Snout\Request;

class RequestTest extends TestCase
{
    public function testGetParser() : void
    {
        $request = new Request('/foo', 'post');
        $this->assertEquals(
            new Parser(
                \Snout\array_to_map([
                    'invalid' => [
                        'SPACE',
                        'TAB',
                        'NEW_LINE',
                        'CARRIAGE_RETURN'
                    ]
                ]),
                new Lexer('/foo')
            ),
            $request->getParser()
        );
    }

    public function testGetMethod() : void
    {
        $request = new Request('/foo', 'post');
        $this->assertEquals('post', $request->getMethod());
    }

    public function testInvalidConfigType() : void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            '$config must be an array or an instance of \Ds\Map.'
        );

        $request = new Request('/foo', 'get', 'foo');
    }
}
