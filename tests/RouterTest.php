<?php
namespace Snout\Tests;

use \PHPUnit\Framework\TestCase;
use Snout\Router;

class RouterTest extends TestCase
{
    public function testTest() : void
    {
        $result = [];

        $router = new Router();

        $router->route(
            '/user/{id: int}',
            [
                'get' => function(array $parameters) use ($result) {
                    $result = $parameters;
                }
            ]
        );

        $router->match('/user/21', 'get');

        $this->assetArrayHasKey('id', $result);
        $this->assetInternalType('int', $result['id']);
        $this->assetEquals(21, $result['id']);
    }
}
