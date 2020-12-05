<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

/** @var RouteCollectorInterface $routes */

use Laminas\Diactoros\Response\JsonResponse;
use MakiseCo\Auth\AuthenticatableInterface;
use MakiseCo\Auth\Guard\GuardInterface;
use MakiseCo\Auth\Http\Middleware\AuthenticationMiddleware;
use MakiseCo\Auth\Http\Middleware\AuthorizationMiddleware;
use MakiseCo\Http\HttpServer;
use MakiseCo\Http\Router\RouteCollectorInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

$routes->get('/', function (): JsonResponse {
    return new JsonResponse(['message' => 'Hello, Okabe!']);
});

$routes->get('/shutdown', function (HttpServer $server): JsonResponse {
    $server->stop();

    return new JsonResponse(['message' => 'OK']);
});

$routes->addGroup(
    '/admin',
    [
        'middleware' => [AuthenticationMiddleware::class],
        'attributes' => [
            // auth guard name
            GuardInterface::class => 'token',
        ],
    ],
    static function (RouteCollectorInterface $routes) {
        $routes
            ->get('/', static function (ServerRequestInterface $request): ResponseInterface {
                /** @var AuthenticatableInterface $user */
                $user = $request->getAttribute(AuthenticatableInterface::class);

                return new JsonResponse(['id' => $user->getAuthIdentifier()]);
            });

        $routes
            ->get('/users', static function (ServerRequestInterface $request): ResponseInterface {
                /** @var AuthenticatableInterface $user */
                $user = $request->getAttribute(AuthenticatableInterface::class);

                return new JsonResponse(['id' => $user->getAuthIdentifier()]);
            })
            ->withMiddleware(AuthorizationMiddleware::class)
            // required user roles
            ->withAttribute(AuthorizationMiddleware::ROLES, ['roleName']);
    }
);
