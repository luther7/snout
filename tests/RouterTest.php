<?php
namespace Snout\Tests;

use \PHPUnit\Framework\TestCase;
use Snout\Router;

class RouterTest extends TestCase
{
    public function testTest()
    {
        $router = new Router();
        $router->route('/test/');
    }
}
