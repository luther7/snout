# Snout PHP request router

[![Build Status](https://travis-ci.org/rubberydub/snout.svg?branch=master)](https://travis-ci.org/rubberydub/snout)
[![Coverage Status](https://coveralls.io/repos/github/rubberydub/snout/badge.svg)](https://coveralls.io/github/rubberydub/snout)

Snout is a PHP request router.  

## Contents
- [Features](#features)
- [Installing](#installing)
- [Quick Example](#quick-example)
- [Controllers](#controllers)
- [Routes](#routes)
- [Embedded Parameters](#embedded-parameters)
- [Sub-Routing](#sub-routing)
- [License](#license)

## Features

- Flexible controller types.
- Extraction and type casting of embedded parameters.
- Custom embedded parameter types.
- Combinable route sets allowing sub-routing.

## Installing

Use [Composer](http://getcomposer.org).

```bash
composer require rubberydub/snout
```

## Quick Example

```php
use Snout\Router;
use Snout\Route;
use Snout\Request;
use Snout\Parameter;

$router = new Router();
$router->push(new Route([
    'name'       => 'user',
    'path'       => '/groupId/{group_id: integer}/name/{name: string}',
    'controller' => [
        'get' => function($parameters) {
            echo 'User name:     ' . $parameters->get('name') . PHP_EOL
              .  'User group id: ' . $parameters->get('group_id') . PHP_EOL;
        }
    ]
]));

$route = $router->run(new Request('/groupId/3/name/John, 'get'));
// User name:     John
// User group id: 3
```

## Controllers

Controllers do not need to be a function. The router does not need to run the controllers. It can simply provide the controllers for the matching route and the embedded parameters for you do use as you please.

```php
use Snout\Router;
use Snout\Route;
use Snout\Request;
use Snout\Parameter;
use My\Container;
use My\Controller;

$router = new Router();
$my_container = new Container();
$my_controller = new Controller();

$router->push(new Route([
    'name'       => 'page',
    'path'       => '/pages/{page_id: integer}',
    'controller' => $my_controller
]));

$request = new Request('/page/3', 'get');
$route = $router->match($request);

$controller = $route->getController();
$parameters = $route->getParameters();

$controller->run(
    $my_container,
    $parameters,
    $request->getMethod()
);
```

## Routes

Specifiy routes by creating `\Snout\Route` objects. Routes accept an array or `Ds\Map` of options:  

| Name       | Type            | Description                                                |
| ---------- | --------------- | ---------------------------------------------------------- |
| name       | `string`        | A name for the route                                       |
| path       | `string`        | The path of the route                                      |
| controller | `mixed`         | Map from HTTP method to controller, or a single controller |
| parameters | `array\|\Ds\Map` | Optional custom embedded parameters                        |
| sub_router | `\Ds\Router`    | An optional sub-router                                     |

Example:

```php
$tokens = new Route([
    'name'        => 'tokens',
    'path'        => '/user/{user_id: integer}/tokens',
    'controllers' => ['get' => $get_token_controller]
])
```

## Embedded Parameters

The router will match the path exactly, except for embedded parameters, which it will extract.  

Embedded parameters are specified in a route's path like so:

```
{NAME: TYPE}
```

For example:

```
/collection/{id: integer}
```

By default there are four embedded parameter types: `string`, `boolean`, `integer` and `float`. Extracted parameters are casted to the appropriate type.

#### Optional/Nullable Parameters

Embedded parameters may be optional or nullable by prefixing the type with `?` like so:

```
/optional/{id: ?string}
```

If the parameter is not present or is `'null'` then the extracted parameter will have value `null`.

#### Custom Parameter Types

Custom parameter types are available. Provide them in a routes options like so:

```php
use Snout\Route;

$route = new Route([
    'name'        => 'region',
    'path'        => '/region/{name: label}',
    'controllers' => ['get' => $get_region_controller]
    'parameters' => [
        'label' => [
            'tokens' => [
                'DIGIT', // All digits
                'ALPHA', // All letters
                'UNDERSCORE',
                'HYPHEN
            ],
            'cast' => 'string'
        ]
    ]
]);
```

Available Tokens:

| Name            | Matches     |
| --------------- | ----------- |
| `DIGIT`         | All digits  |
| `ALPHA`         | All letters |
| `UNDERSCORE`    | `_`         |
| `HYPHEN`        | `-`         |
| `PERIOD`        | `.`         |
| `COLON`         | `:`         |
| `OPEN_BRACE`    | `{`         |
| `CLOSE_BRACE`   | `}`         |
| `OPEN_BRACKET`  | `[`         |
| `CLOSE_BRACKET` | `]`         |
| `BACK_SLASH`    | `\`         |

## Sub-Routing

Routers can be combined to allow sub-routing at a particalar part of the path. Parent router's controllers can be extracted and executed before the sub routing.


```php
use Snout\Router;
use Snout\Route;
use Snout\Request;
use Snout\Parameter;

$users_router = new Router();
$users_router->push(new Route([
    'name'        => 'user_by_id',
    'path'        => '/{id: integer}',
    'controllers' => [
        'get' => function ($parameters) {
            echo 'User id: ' . $parameters->get('id') . PHP_EOL;
        }
    ]
]));

$users_router->push(new Route([
    'name'        => 'user_by_name',
    'path'        => '/{name: string}',
    'controllers' => [
        'get' => function ($parameters) {
            echo 'User name: ' . $parameters->get('name') . PHP_EOL;
        }
    ]
]));

$router = new Router();
$router->push(new Route([
    'name'        => 'region,
    'path'        => '/{region: string}/user/',
    'controllers' => [
        'get' => function ($parameters) {
            echo 'Region: ' . $parameters->get('region') . PHP_EOL;
        }
    ]
    'sub_router' => $users_router
]));

$request = new Request('/victoria/user/21', 'get');
$route = $router->match($request);
$controller = $route->getController($request->getMethod());
$parameters = $route->getParameters();
$controller($route->getParameters());
// Region: victoria

$sub_router = $route->getSubRouter();
$sub_route = $sub_router->match($request);
$sub_controller = $sub_route->getController($request->getMethod());
$sub_controller($parameters);
// User id: 21

// Or using run method:
$router->run(new Request('/victoria/user/21', 'get'));
// Region: victoria
// User id: 21
```

## License

MIT © [Franz Neulist Carroll](mailto:franzneulistcarroll@gmail.com)
