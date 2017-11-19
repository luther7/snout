<?php
namespace Snout\Tests;

use PHPUnit\Framework\TestCase;
use Snout\StringIterator;

class StringIteratorTest extends TestCase
{
    public static function stringIteratorProvider() : array
    {
        return [[new StringIterator('foobar')]];
    }

    /**
     * @dataProvider stringIteratorProvider
     */
    public function testWhile(StringIterator $iterator) : void
    {
        while ($iterator->valid()) {
            $current = $iterator->current();
            $index = $iterator->key();

            switch ($index) {
                case 0:
                    $this->assertEquals('f', $current);
                    break;

                case 1:
                    // fallthrough
                case 2:
                    $this->assertEquals('o', $current);
                    break;

                case 3:
                    $this->assertEquals('b', $current);
                    break;

                case 4:
                    $this->assertEquals('a', $current);
                    break;

                case 5:
                    $this->assertEquals('r', $current);
                    break;
            }

            $iterator->next();
        }
    }

    /**
     * @dataProvider stringIteratorProvider
     */
    public function testForeach(StringIterator $iterator) : void
    {
        foreach ($iterator as $index => $next) {
            switch ($index) {
                case 0:
                    $this->assertEquals('f', $next);
                    break;

                case 1:
                case 2:
                    $this->assertEquals('o', $next);
                    break;

                case 3:
                    $this->assertEquals('b', $next);
                    break;

                case 4:
                    $this->assertEquals('a', $next);
                    break;

                case 5:
                    $this->assertEquals('r', $next);
                    break;
            }
        }
    }
}
