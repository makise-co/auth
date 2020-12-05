<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo\Auth\Tests;

use MakiseCo\Application;
use MakiseCo\Auth\AuthManager;
use MakiseCo\Auth\Guard\BearerTokenGuard;
use MakiseCo\Auth\Tests\Http\Stubs\FakeUserProvider;
use PHPUnit\Framework\TestCase;

class AuthServiceProviderTest extends TestCase
{
    public function testItWorks(): void
    {
        putenv('HTTP_WORKER_NUM=1');
        putenv('HTTP_REACTOR_NUM=1');

        $application = new Application(
            dirname(__DIR__) . '/',
            dirname(__DIR__) . '/config'
        );

        $am = $application->getContainer()->get(AuthManager::class);
        self::assertInstanceOf(BearerTokenGuard::class, $am->getGuard('token'));
        self::assertInstanceOf(FakeUserProvider::class, $am->getProvider('database'));
    }
}
