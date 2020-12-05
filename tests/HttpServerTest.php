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
use MakiseCo\Http\Events\ServerStarted;
use MakiseCo\Http\HttpServer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;

class HttpServerTest extends TestCase
{
    public function testItWorks(): void
    {
        putenv('HTTP_WORKER_NUM=1');
        putenv('HTTP_REACTOR_NUM=1');

        $application = new Application(
            dirname(__DIR__),
            dirname(__DIR__) . '/config'
        );

        $container = $application->getContainer();

        $pm = new ProcessManager();
        $pm->parentFunc = static function () use ($pm) {
            try {
                file_get_contents("http://127.0.0.1:{$pm->getFreePort()}/admin");
            } catch (\Throwable $e) {
                if (false !== \strpos($e->getMessage(), '401 Unauthorized')) {
                    echo "401";
                }
            }

            $opts = [
                "http" => [
                    "method" => "GET",
                    "header" => "Authorization: Bearer secretToken\r\n"
                ]
            ];

            $context = stream_context_create($opts);

            $response = file_get_contents("http://127.0.0.1:{$pm->getFreePort()}/admin", false, $context);
            echo $response;

            try {
                file_get_contents("http://127.0.0.1:{$pm->getFreePort()}/admin/users", false, $context);
            } catch (\Throwable $e) {
                if (false !== \strpos($e->getMessage(), '403 Forbidden')) {
                    echo "403";
                }
            }

            $opts = [
                "http" => [
                    "method" => "GET",
                    "header" => "Authorization: Bearer secretTokenWithRoles\r\n"
                ]
            ];

            $context = stream_context_create($opts);

            $response = file_get_contents("http://127.0.0.1:{$pm->getFreePort()}/admin/users", false, $context);
            echo $response;

            file_get_contents("http://127.0.0.1:{$pm->getFreePort()}/shutdown");
        };
        $pm->childFunc = static function () use ($pm, $container) {
            $server = $container->get(HttpServer::class);

            $container->get(EventDispatcher::class)->addListener(ServerStarted::class, function () use ($pm) {
                $pm->wakeup();
            });

            $server->start('127.0.0.1', $pm->getFreePort());
        };

        $pm->setWaitTimeout(3);
        $pm->childFirst();

        ob_start();
        $pm->run();
        $output = ob_get_clean();

        self::assertSame('401{"id":322}403{"id":322}', $output);
    }
}
