<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

return [
    'guards' => [
        // guard name
        'token' => [
            'class' => \MakiseCo\Auth\Guard\BearerTokenGuard::class,
            'provider' => 'database',
            'storageKey' => 'token',
        ]
    ],

    'providers' => [
        // user provider name
        'database' => [
            'class' => \MakiseCo\Auth\Tests\Http\Stubs\FakeUserProvider::class
        ],
    ]
];
