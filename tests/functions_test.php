<?php
namespace Snout\Tests;

use Ds\Map;

class FunctionsTest extends TestCase
{
    public function testDecodeFile() : void
    {
        $test_config = \Snout\json_decode_file(
            __DIR__ . '/configs/misc.json'
        );

        $check = new Map([
            'foo' => 'bar',
            'baz' => new Map([
                'snap' => new Map([
                    'pop',
                    1234,
                    true,
                    false
                ])
            ]),
            'dog' => 5678,
            'cat' => 9123
        ]);

        $this->assertEquals($test_config);
    }

    public function testBadPathException() : void
    {
        $this->expectException(Exception::class);

        $test_config = \Snout\json_decode_file(__DIR__ . '/foo');
    }

    public function testBadPathNull() : void
    {
        $test_config = \Snout\json_decode_file(__DIR__ . '/foo', false);

        $this->assertNull($test_config);
    }

    public function testInvalidJSONException() : void
    {
        $this->expectException(Exception::class);

        $test_config = \Snout\json_decode_file(
            __DIR__ . '/configs/invalid.json'
        );
    }

    public function testInvalidJSONNull() : void
    {
        $test_config = \Snout\json_decode_file(
            __DIR__ . '/configs/invalid.json',
            false
        );

        $this->assertNull($test_config);
    }
}
