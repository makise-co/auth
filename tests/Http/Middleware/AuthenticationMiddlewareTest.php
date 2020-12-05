<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo\Auth\Tests\Http\Middleware;

use Laminas\Diactoros\Response\TextResponse;
use Laminas\Diactoros\ServerRequest;
use MakiseCo\Auth\AuthenticatableInterface;
use MakiseCo\Auth\AuthManager;
use MakiseCo\Auth\Exceptions\UnauthenticatedException;
use MakiseCo\Auth\Guard\GuardInterface;
use MakiseCo\Auth\Http\Middleware\AuthenticationMiddleware;
use MakiseCo\Http\Router\Route;
use MakiseCo\Http\Router\RouteInterface;
use MakiseCo\Auth\Tests\Http\Stubs\AuthFailedGuard;
use MakiseCo\Auth\Tests\Http\Stubs\AuthSuccessGuard;
use MakiseCo\Auth\Tests\Http\Stubs\EmptyUserProvider;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class AuthenticationMiddlewareTest extends TestCase
{
    public function testAuthSuccessful(): void
    {
        $middleware = new AuthenticationMiddleware($this->getAuthManager());

        $request = new ServerRequest(
            [],
            [],
            '/',
            'GET'
        );

        $route = new Route(['GET'], '/', fn() => 1);
        $route
            ->withAttribute(GuardInterface::class, 'success');

        $request = $request
            ->withAttribute(RouteInterface::class, $route)
            ->withAttribute('test', $this);

        $handler = new class implements RequestHandlerInterface {
            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                /* @var AuthenticationMiddlewareTest $test */
                $test = $request->getAttribute('test');

                /* @var AuthenticatableInterface $user */
                $user = $request->getAttribute(AuthenticatableInterface::class);

                $test::assertNotNull($user);
                $test::assertInstanceOf(AuthenticatableInterface::class, $user);
                $test::assertEquals(1, $user->getAuthIdentifier());

                return new TextResponse('');
            }
        };

        $middleware->process($request, $handler);
    }

    public function testUnauthorized(): void
    {
        $middleware = new AuthenticationMiddleware($this->getAuthManager());

        $request = new ServerRequest(
            [],
            [],
            '/',
            'GET'
        );

        $route = new Route(['GET'], '/', fn() => 1);
        $route
            ->withAttribute(GuardInterface::class, 'fail');

        $request = $request
            ->withAttribute(RouteInterface::class, $route)
            ->withAttribute('test', $this);

        $handler = new class implements RequestHandlerInterface {
            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                return new TextResponse('');
            }
        };

        $this->expectException(UnauthenticatedException::class);
        $middleware->process($request, $handler);
    }

    public function testMultipleGuardsSuccess(): void
    {
        $authManager = $this->getAuthManager();
        $middleware = new AuthenticationMiddleware($authManager);

        $request = new ServerRequest(
            [],
            [],
            '/',
            'GET'
        );

        $route = new Route(['GET'], '/', fn() => 1);
        $route
            ->withAttribute(GuardInterface::class, ['fail', 'success']);

        $request = $request
            ->withAttribute(RouteInterface::class, $route)
            ->withAttribute('test', $this);

        $handler = new class implements RequestHandlerInterface {
            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                /* @var AuthenticationMiddlewareTest $test */
                $test = $request->getAttribute('test');

                /* @var AuthenticatableInterface $user */
                $user = $request->getAttribute(AuthenticatableInterface::class);

                $test::assertNotNull($user);
                $test::assertInstanceOf(AuthenticatableInterface::class, $user);
                $test::assertEquals(1, $user->getAuthIdentifier());

                return new TextResponse('');
            }
        };

        $middleware->process($request, $handler);

        /* @var AuthFailedGuard $authFailedGuard */
        $authFailedGuard = $authManager->getGuard('fail');
        /* @var AuthSuccessGuard $authSuccessGuard */
        $authSuccessGuard = $authManager->getGuard('success');

        self::assertTrue($authFailedGuard->isCalled());
        self::assertTrue($authSuccessGuard->isCalled());
    }

    protected function getAuthManager(): AuthManager
    {
        $authManager = new AuthManager();
        $authManager->addProvider('test', new EmptyUserProvider());

        $authManager->addGuard('success', new AuthSuccessGuard());
        $authManager->addGuard('fail', new AuthFailedGuard());

        return $authManager;
    }
}
