<?php
namespace Snout\Tests;

use \PHPUnit\Framework\TestCase;
use Snout\Config;
use Snout\Exceptions\ConfigException;

class ConfigTest extends TestCase
{
    public function testConfig()
    {
        $test_config = __DIR__ . '/test_config.json';
        $config = new Config($test_config);

        $this->assertEquals(null, $config->get('crack'));
        $this->assertEquals('bar', $config->get('foo'));

        $snap = $config->get('baz')['snap'];
        $this->assertEquals('pop', $snap[0]);
        $this->assertEquals(1234, $snap[1]);
        $this->assertTrue($snap[2]);
        $this->assertFalse($snap[3]);

    }

    /**
     * @expectedException Snout\Exceptions\ConfigException
     */
    public function testBadPathException()
    {
        $test_config = __DIR__ . '/foo';
        $config = new Config($test_config);
    }

    /**
     * @expectedException Snout\Exceptions\ConfigException
     */
    public function testInvalidJSONException()
    {
        $test_config = __DIR__ . '/test_config_invalid_json.json';
        $config = new Config($test_config);
    }

    /**
     * @expectedException Snout\Exceptions\ConfigException
     */
    public function testAssertException()
    {
        $test_config = __DIR__ . '/test_config.json';
        $config = new Config($test_config);

        $this->assertEquals('foo', $config->get('crack', true));
    }
}