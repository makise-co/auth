<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo\Auth\Tests\Http\Stubs;

use MakiseCo\Auth\AuthenticatableInterface;
use MakiseCo\Auth\AuthorizableInterface;
use MakiseCo\Auth\UserProviderInterface;

class FakeUserProvider implements UserProviderInterface
{
    public function retrieveById($id): ?AuthenticatableInterface
    {
        return null;
    }

    public function retrieveByCredentials(array $credentials): ?AuthenticatableInterface
    {
        $token = $credentials['token'] ?? null;
        if ($token === null) {
            return null;
        }

        if ($token !== 'secretToken' && $token !== 'secretTokenWithRoles') {
            return null;
        }

        $hasRole = $token === 'secretTokenWithRoles';

        return new class($hasRole) implements AuthenticatableInterface, AuthorizableInterface {
            private bool $hasRole;

            public function __construct(bool $hasRole)
            {
                $this->hasRole = $hasRole;
            }

            public function getAuthIdentifier(): int
            {
                return 322;
            }

            public function hasRole(string $role): bool
            {
                return $this->hasRole;
            }

            public function hasAllRoles(array $roles): bool
            {
                return $this->hasRole;
            }

            public function hasAnyRoles(array $roles): bool
            {
                return $this->hasRole;
            }

            public function hasAllPermissions(array $permissions): bool
            {
                return false;
            }

            public function hasPermission(string $permission): bool
            {
                return false;
            }

            public function hasAnyPermissions(array $permissions): bool
            {
                return false;
            }
        };
    }
}
