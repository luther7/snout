<?php
namespace Snout\Tests;

use \PHPUnit\Framework\TestCase;
use Snout\Exceptions\ConfigException;
use Snout\Config;

class ConfigTest extends TestCase
{
    public function testGet()
    {
        $test_config = \Snout\json_decode_file(
            __DIR__ . '/test_configs/test.json',
            true
        );

        $config = new Config($test_config);

        $this->assertEquals(null, $config->get('crack'));
        $this->assertEquals('bar', $config->get('foo'));

        $snap = $config->get('baz')['snap'];
        $this->assertEquals('pop', $snap[0]);
        $this->assertEquals(1234, $snap[1]);
        $this->assertTrue($snap[2]);
        $this->assertFalse($snap[3]);

        $test = $config->get(['dog', 'cat']);
        $this->assertEquals(5678, $test['dog']);
        $this->assertEquals(9123, $test['cat']);
    }

    public function testSet()
    {
        $test_config = \Snout\json_decode_file(
            __DIR__ . '/test_configs/test.json',
            true
        );

        $config = new Config($test_config);

        $config->set('pig', 1234);
        $this->assertEquals(1234, $config->get('pig'));

        $config->set([
            'seal' => 5678,
            'bird' => 9123
        ]);

        $test = $config->get(['seal', 'bird']);
        $this->assertEquals(5678, $test['seal']);
        $this->assertEquals(9123, $test['bird']);
    }

    public function testAssertException()
    {
        $this->expectException(ConfigException::class);
        $this->expectExceptionMessage(
            "Required config value is missing: crack."
        );

        $test_config = \Snout\json_decode_file(
            __DIR__ . '/test_configs/test.json',
            true
        );

        $config = new Config($test_config);
        $config->get('crack', true);
    }

    public function testGetInvalidArgument()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            "Argument 'option' must be a string or an array of strings."
        );

        $test_config = \Snout\json_decode_file(
            __DIR__ . '/test_configs/test.json',
            true
        );

        $config = new Config($test_config);
        $config->get(1234);
    }

    public function testSetInvalidArgument1()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            "Argument 'option' must be a string or an array of option value "
            . "pairs."
        );

        $test_config = \Snout\json_decode_file(
            __DIR__ . '/test_configs/test.json',
            true
        );

        $config = new Config($test_config);

        $config->set(1234);
    }

    public function testSetInvalidArgument2()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Second argument 'value' required.");

        $test_config = \Snout\json_decode_file(
            __DIR__ . '/test_configs/test.json',
            true
        );

        $config = new Config($test_config);

        $config->set('foo');
    }
}
