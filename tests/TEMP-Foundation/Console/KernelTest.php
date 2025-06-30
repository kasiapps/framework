<?php

use Kasi\Console\Events\CommandFinished;
use Kasi\Console\Events\CommandStarting;
use Kasi\Contracts\Console\Kernel as ConsoleKernelContract;
use Kasi\Contracts\Debug\ExceptionHandler as ExceptionHandlerContract;
use Kasi\Foundation\Application;
use Kasi\Foundation\Console\Kernel as ConsoleKernel;
use Kasi\Foundation\Exceptions\Handler as ExceptionHandler;

class KernelTest extends \Kasi\Foundation\Testing\TestCase
{
    /**
     * Creates the application.
     *
     * Needs to be implemented by subclasses.
     *
     * @return \Symfony\Component\HttpKernel\HttpKernelInterface
     */
    public function createApplication()
    {
        $app = new Application();

        $app->configure('app');

        $app->singleton(ExceptionHandlerContract::class, fn () => new ExceptionHandler());
        $app->singleton(ConsoleKernelContract::class, function () use ($app) {
            return tap(new ConsoleKernel($app), function ($kernel) {
                $kernel->rerouteSymfonyCommandEvents();
            });
        });

        return $app;
    }

    public function testItCanRerouteToSymfonyEvent()
    {
        $this->expectsEvents([CommandStarting::class, CommandFinished::class]);

        $this->artisan('cache:forget', ['key' => 'kasi']);
    }
}
