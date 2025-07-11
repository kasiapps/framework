<?php

namespace Kasi\Tests\Integration\Foundation\Console;

use Kasi\Testing\Assert;
use Orchestra\Testbench\Attributes\WithEnv;
use Orchestra\Testbench\TestCase;

use function Orchestra\Testbench\remote;

class AboutCommandTest extends TestCase
{
    public function testItCanDisplayAboutCommandAsJson()
    {
        $process = remote('about --json', ['APP_ENV' => 'local'])->mustRun();

        tap(json_decode($process->getOutput(), true), function ($output) {
            Assert::assertArraySubset([
                'application_name' => 'Kasi',
                'php_version' => PHP_VERSION,
                'environment' => 'local',
                'debug_mode' => true,
                'url' => 'localhost',
                'maintenance_mode' => false,
            ], $output['environment']);

            Assert::assertArraySubset([
                'config' => false,
                'events' => false,
                'routes' => false,
            ], $output['cache']);

            Assert::assertArraySubset([
                'broadcasting' => 'log',
                'cache' => 'database',
                'database' => 'testing',
                'logs' => ['single'],
                'mail' => 'log',
                'queue' => 'database',
                'session' => 'cookie',
            ], $output['drivers']);
        });
    }

    #[WithEnv('VIEW_COMPILED_PATH', __DIR__.'/../../View/templates')]
    public function testItRespectsCustomPathForCompiledViews(): void
    {
        $process = remote('about --json', ['APP_ENV' => 'local'])->mustRun();

        tap(json_decode($process->getOutput(), true), static function (array $output) {
            Assert::assertArraySubset([
                'views' => true,
            ], $output['cache']);
        });
    }
}
