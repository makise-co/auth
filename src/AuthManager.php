<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo\Auth;

use MakiseCo\Auth\Guard\GuardInterface;

use function sprintf;

class AuthManager
{
    /**
     * @var UserProviderInterface[]
     */
    protected array $providers = [];

    /**
     * @var GuardInterface[]
     */
    protected array $guards = [];

    public function addProvider(string $name, UserProviderInterface $provider): void
    {
        $this->providers[$name] = $provider;
    }

    public function addGuard(string $name, GuardInterface $guard): void
    {
        $this->guards[$name] = $guard;
    }

    public function getProvider(string $name): UserProviderInterface
    {
        $provider = $this->providers[$name] ?? null;
        if (null === $provider) {
            throw new \InvalidArgumentException(sprintf('Provider %s not found', $name));
        }

        return $provider;
    }

    public function getGuard(string $name): GuardInterface
    {
        $guard = $this->guards[$name] ?? null;
        if (null === $guard) {
            throw new \InvalidArgumentException(sprintf('Guard %s not found', $name));
        }

        return $guard;
    }
}
