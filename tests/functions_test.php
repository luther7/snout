<?php
namespace Snout\Tests;

use PHPUnit\Framework\TestCase;
use Ds\Map;
use Ds\Set;
use Snout\Exceptions\ConfigurationException;

class FunctionsTest extends TestCase
{
    public static function configProvider() : array
    {
        return [
           [
               new Map([
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
               ])
           ]
        ];
    }

    /**
     * @dataProvider configProvider
     */
    public function testArrayToMap(Map $test) : void
    {
        $result = \Snout\array_to_map([
            'foo' => 'bar',
            'baz' => [
                'snap' => [
                    'pop',
                    1234,
                    true,
                    false
                ]
            ],
            'dog' => 5678,
            'cat' => 9123
        ]);

        $this->assertEquals($test, $result);
    }

    /**
     * @dataProvider configProvider
     */
    public function testDecodeFile(Map $test) : void
    {
        $config = \Snout\json_decode_file(__DIR__ . '/configs/misc.json');

        $this->assertEquals($test, $config);
    }

    public function testBadPathException() : void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('File not found: ' . __DIR__ . '/foo');

        $test_config = \Snout\json_decode_file(__DIR__ . '/foo');
    }

    public function testBadPathNull() : void
    {
        $test_config = \Snout\json_decode_file(__DIR__ . '/foo', false);

        $this->assertNull($test_config);
    }

    public function testInvalidJSONException() : void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Could not decode JSON.');

        $test_config = \Snout\json_decode_file(
            __DIR__ . '/configs/invalid_json.json'
        );
    }

    public function testInvalidJSONNull() : void
    {
        $test_config = \Snout\json_decode_file(
            __DIR__ . '/configs/invalid_json.json',
            false
        );

        $this->assertNull($test_config);
    }

    public function testCheckConfigValid() : void
    {
        $this->assertNull(
            \Snout\check_config(
                new Set([
                    'foo',
                    'baz',
                    'dog',
                    'cat'
                ]),
                $config = \Snout\json_decode_file(__DIR__ . '/configs/misc.json')
            )
        );
    }

    public function testCheckConfigInvalid() : void
    {
        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage(
            "Invalid configuration. Missing keys: 'bar'."
        );

        $this->assertNull(
            \Snout\check_config(
                new Set(['bar']),
                $config = \Snout\json_decode_file(__DIR__ . '/configs/misc.json')
            )
        );
    }
}
