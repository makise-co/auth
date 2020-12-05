# Makise-Co Auth
Authentication and Authorization implementation

## Installation
* Register service provider - `MakiseCo\Auth\AuthServiceProvider`
* Minimal required configuration [config](config)

## Example configuration
```php
// config/auth.php

return [
    'guards' => [
        // guard name
        'token' => [
            'class' => \MakiseCo\Auth\Guard\BearerTokenGuard::class,
            'provider' => 'database',
        ]
    ],

    'providers' => [
        // user provider name
        'database' => [
            // your own user provider (should implement UserProviderInterface)
            'class' => \App\Auth\MyUserProvider::class,
        ],
    ]
];
```

## Usage
```php
// your routes file

use MakiseCo\Auth\Guard\GuardInterface;
use MakiseCo\Auth\Http\Middleware\AuthorizationMiddleware;
use MakiseCo\Http\Router\RouteCollectorInterface;

/** @var RouteCollectorInterface $routes */

$routes->addGroup(
    '/admin',
    [
        'namespace' => 'App\\Http\\Controller\\Admin\\',
        'middleware' => [
            \MakiseCo\Auth\Http\Middleware\AuthenticationMiddleware::class,
        ],
        'attributes' => [
            // auth guard name
            GuardInterface::class => 'token',
        ],
    ],
    function (RouteCollectorInterface $routes) {
        $routes
            ->get('/users', 'UserController@index')
            // check user's access rights
            ->withMiddleware(AuthorizationMiddleware::class)
            // user must have an "admin" role
            ->withAttribute(AuthorizationMiddleware::ROLES, ['admin']);
    }
);
```
