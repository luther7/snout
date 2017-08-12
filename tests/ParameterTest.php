<?php
namespace Snout\Tests;

use PHPUnit\Framework\TestCase;
use Snout\Parameter;

class ParameterTest extends TestCase
{
    public function test() : void
    {
        $parameter = new Parameter('test', 'string', 'foo');

        $this->assertEquals('test', $parameter->getName());
        $this->assertEquals('string', $parameter->getType());
        $this->assertEquals('foo', $parameter->getValue());
    }
}
