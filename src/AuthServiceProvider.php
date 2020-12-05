<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo\Auth;

use DI\Container;
use InvalidArgumentException;
use MakiseCo\Auth\Guard\GuardInterface;
use MakiseCo\Config\ConfigRepositoryInterface;
use MakiseCo\Providers\ServiceProviderInterface;

use function array_key_exists;
use function is_array;
use function is_string;

class AuthServiceProvider implements ServiceProviderInterface
{
    public function register(Container $container): void
    {
        $container->set(AuthManager::class, function (Container $container, ConfigRepositoryInterface $config) {
            $authManager = new AuthManager();

            $this->registerProviders($authManager, $container, $config->get('auth.providers', []));
            $this->registerGuards($authManager, $container, $config->get('auth.guards', []));

            return $authManager;
        });
    }

    private function registerProviders(AuthManager $authManager, Container $container, array $providers): void
    {
        foreach ($providers as $name => $params) {
            if (is_array($params) && array_key_exists('class', $params)) {
                $class = $params['class'];
                unset($params['class']);
            } elseif (is_string($params)) {
                $class = $params;
                $params = [];
            } else {
                throw new InvalidArgumentException("Wrong provider \"{$name}\" configuration");
            }

            $provider = $container->make($class, $params);
            if (!$provider instanceof UserProviderInterface) {
                throw new InvalidArgumentException(
                    sprintf(
                        'Wrong user provider "%s" - %s is not an instance of %s',
                        $name,
                        $class,
                        UserProviderInterface::class,
                    )
                );
            }

            $authManager->addProvider($name, $provider);
        }
    }

    private function registerGuards(AuthManager $authManager, Container $container, array $guards): void
    {
        foreach ($guards as $name => $params) {
            ['class' => $class, 'provider' => $provider] = $params;
            unset($params['class'], $params['provider']);

            try {
                $providerInstance = $authManager->getProvider($provider);
            } catch (InvalidArgumentException $e) {
                throw new InvalidArgumentException(
                    sprintf(
                        'User provider "%s" - not found for guard "%s"',
                        $provider,
                        $name
                    )
                );
            }

            $args = ['provider' => $providerInstance];
            $args += $params;

            $guard = $container->make($class, $args);
            if (!$guard instanceof GuardInterface) {
                throw new InvalidArgumentException(
                    sprintf(
                        'Wrong guard "%s" - %s is not an instance of %s',
                        $name,
                        $class,
                        GuardInterface::class,
                    )
                );
            }

            $authManager->addGuard($name, $guard);
        }
    }
}
