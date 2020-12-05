<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo\Auth\Tests;

use MakiseCo\Auth\AuthManager;
use MakiseCo\Auth\Tests\Http\Stubs\CustomGuard;
use MakiseCo\Auth\Tests\Http\Stubs\CustomUserProvider;
use PHPUnit\Framework\TestCase;

class AuthManagerTest extends TestCase
{
    public function testAddProvider(): void
    {
        $authManager = $this->getAuthManager();

        $authManager->addProvider(
            'some',
            new CustomUserProvider(120),
        );

        /* @var CustomUserProvider $provider */
        $provider = $authManager->getProvider('some');

        self::assertInstanceOf(CustomUserProvider::class, $provider);
        self::assertEquals(120, $provider->getCacheTtl());
    }

    public function testAddGuard(): void
    {
        $authManager = $this->getAuthManager();

        $authManager->addProvider(
            'some',
            $provider = new CustomUserProvider(120),
        );

        $authManager->addGuard(
            'sso',
            new CustomGuard($provider, true),
        );

        /* @var CustomGuard $guard */
        $guard = $authManager->getGuard('sso');

        self::assertInstanceOf(CustomGuard::class, $guard);
    }

    protected function getAuthManager(): AuthManager
    {
        return new AuthManager();
    }
}
