<?php
namespace Snout\Tests;

use \PHPUnit\Framework\TestCase;
use Snout\Utilities\StringIterator;

class StringIteratorTest extends TestCase
{
    public function testWhile()
    {
        $iterator = new StringIterator('foobar');

        while ($iterator->valid()) {
            $current = $iterator->current();
            $index = $iterator->key();

            switch ($index) {
                case 0:
                    $this->assertEquals('f', $current);
                    break;

                case 1:
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

    public function testForeach()
    {
        $iterator = new StringIterator('foobar');

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
