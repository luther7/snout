<?php
namespace Snout\Tests;

class FunctionsTest extends TestCase
{
    public function testDecodeFile() : void
    {
        $test_config = \Snout\json_decode_file(
            __DIR__ . '/configs/misc.json',
            true
        );

        $this->assertNotNull($test_config);
    }

    /**
     * @expectedException \Exception
     */
    public function testBadPathException() : void
    {
        $test_config = \Snout\json_decode_file(__DIR__ . '/foo', true);
    }

    public function testBadPathNull() : void
    {
        $test_config = \Snout\json_decode_file(__DIR__ . '/foo', true, false);

        $this->assertNull($test_config);
    }

    /**
     * @expectedException \Exception
     */
    public function testInvalidJSONException() : void
    {
        $test_config = \Snout\json_decode_file(
            __DIR__ . '/configs/invalid.json',
            true
        );
    }

    public function testInvalidJSONNull() : void
    {
        $test_config = \Snout\json_decode_file(
            __DIR__ . '/configs/invalid.json',
            true,
            false
        );

        $this->assertNull($test_config);
    }
}
