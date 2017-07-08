<?php
namespace Snout\Tests;

class FunctionsTest extends TestCase
{
    public function testDecodeFile()
    {
        $test_config = \Snout\json_decode_file(
            __DIR__ . '/test_configs/test.json',
            true
        );

        $this->assertNotNull($test_config);
    }

    /**
     * @expectedException \Exception
     */
    public function testBadPathException()
    {
        $test_config = \Snout\json_decode_file(__DIR__ . '/foo', true);
    }

    public function testBadPathNull()
    {
        $test_config = \Snout\json_decode_file(__DIR__ . '/foo', true, false);

        $this->assertNull($test_config);
    }

    /**
     * @expectedException \Exception
     */
    public function testInvalidJSONException()
    {
        $test_config = \Snout\json_decode_file(
            __DIR__ . '/test_configs/test_invalid.json',
            true
        );
    }

    public function testInvalidJSONNull()
    {
        $test_config = \Snout\json_decode_file(
            __DIR__ . '/test_configs/test_config_invalid.json',
            true,
            false
        );

        $this->assertNull($test_config);
    }
}
