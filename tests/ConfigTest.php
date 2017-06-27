<?php
namespace Snout\Tests;

use \PHPUnit\Framework\TestCase;
use Snout\Config;
use Snout\Exceptions\ConfigException;

class ConfigTest extends TestCase
{
    public function testGet()
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

        $test = $config->get(['dog', 'cat']);
        $this->assertEquals(5678, $test['dog']);
        $this->assertEquals(9123, $test['cat']);
    }

    public function testSet()
    {
        $test_config = __DIR__ . '/test_config.json';
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
        $config->get('crack', true);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testGetInvalidArgument()
    {
        $test_config = __DIR__ . '/test_config.json';
        $config = new Config($test_config);

        $config->get(1234);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testSetInvalidArgument()
    {
        $test_config = __DIR__ . '/test_config.json';
        $config = new Config($test_config);

        $config->set(1234);
    }
}