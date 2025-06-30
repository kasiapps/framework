<?php

namespace Kasi\Tests\Foundation\Http;

use Kasi\Events\Dispatcher;
use Kasi\Foundation\Application;
use Kasi\Foundation\Events\Terminating;
use Kasi\Foundation\Http\Kernel;
use Kasi\Http\Request;
use Kasi\Http\Response;
use Kasi\Routing\Router;
use PHPUnit\Framework\TestCase;

class KernelTest extends TestCase
{
    public function testGetMiddlewareGroups()
    {
        $kernel = new Kernel($this->getApplication(), $this->getRouter());

        $this->assertEquals([], $kernel->getMiddlewareGroups());
    }

    public function testGetRouteMiddleware()
    {
        $kernel = new Kernel($this->getApplication(), $this->getRouter());

        $this->assertEquals([], $kernel->getRouteMiddleware());
    }

    public function testGetMiddlewarePriority()
    {
        $kernel = new Kernel($this->getApplication(), $this->getRouter());

        $this->assertEquals([
            \Kasi\Foundation\Http\Middleware\HandlePrecognitiveRequests::class,
            \Kasi\Cookie\Middleware\EncryptCookies::class,
            \Kasi\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Kasi\Session\Middleware\StartSession::class,
            \Kasi\View\Middleware\ShareErrorsFromSession::class,
            \Kasi\Contracts\Auth\Middleware\AuthenticatesRequests::class,
            \Kasi\Routing\Middleware\ThrottleRequests::class,
            \Kasi\Routing\Middleware\ThrottleRequestsWithRedis::class,
            \Kasi\Contracts\Session\Middleware\AuthenticatesSessions::class,
            \Kasi\Routing\Middleware\SubstituteBindings::class,
            \Kasi\Auth\Middleware\Authorize::class,
        ], $kernel->getMiddlewarePriority());
    }

    public function testAddToMiddlewarePriorityAfter()
    {
        $kernel = new Kernel($this->getApplication(), $this->getRouter());

        $kernel->addToMiddlewarePriorityAfter(
            [
                \Kasi\Cookie\Middleware\EncryptCookies::class,
                \Kasi\Contracts\Auth\Middleware\AuthenticatesRequests::class,
            ],
            \Kasi\Routing\Middleware\ValidateSignature::class,
        );

        $this->assertEquals([
            \Kasi\Foundation\Http\Middleware\HandlePrecognitiveRequests::class,
            \Kasi\Cookie\Middleware\EncryptCookies::class,
            \Kasi\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Kasi\Session\Middleware\StartSession::class,
            \Kasi\View\Middleware\ShareErrorsFromSession::class,
            \Kasi\Contracts\Auth\Middleware\AuthenticatesRequests::class,
            \Kasi\Routing\Middleware\ValidateSignature::class,
            \Kasi\Routing\Middleware\ThrottleRequests::class,
            \Kasi\Routing\Middleware\ThrottleRequestsWithRedis::class,
            \Kasi\Contracts\Session\Middleware\AuthenticatesSessions::class,
            \Kasi\Routing\Middleware\SubstituteBindings::class,
            \Kasi\Auth\Middleware\Authorize::class,
        ], $kernel->getMiddlewarePriority());
    }

    public function testAddToMiddlewarePriorityBefore()
    {
        $kernel = new Kernel($this->getApplication(), $this->getRouter());

        $kernel->addToMiddlewarePriorityBefore(
            [
                \Kasi\Cookie\Middleware\EncryptCookies::class,
                \Kasi\Contracts\Auth\Middleware\AuthenticatesRequests::class,
            ],
            \Kasi\Routing\Middleware\ValidateSignature::class,
        );

        $this->assertEquals([
            \Kasi\Foundation\Http\Middleware\HandlePrecognitiveRequests::class,
            \Kasi\Routing\Middleware\ValidateSignature::class,
            \Kasi\Cookie\Middleware\EncryptCookies::class,
            \Kasi\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Kasi\Session\Middleware\StartSession::class,
            \Kasi\View\Middleware\ShareErrorsFromSession::class,
            \Kasi\Contracts\Auth\Middleware\AuthenticatesRequests::class,
            \Kasi\Routing\Middleware\ThrottleRequests::class,
            \Kasi\Routing\Middleware\ThrottleRequestsWithRedis::class,
            \Kasi\Contracts\Session\Middleware\AuthenticatesSessions::class,
            \Kasi\Routing\Middleware\SubstituteBindings::class,
            \Kasi\Auth\Middleware\Authorize::class,
        ], $kernel->getMiddlewarePriority());
    }

    public function testItTriggersTerminatingEvent()
    {
        $called = [];
        $app = $this->getApplication();
        $events = new Dispatcher($app);
        $app->instance('events', $events);
        $kernel = new Kernel($app, $this->getRouter());
        $app->instance('terminating-middleware', new class($called)
        {
            public function __construct(private &$called)
            {
                //
            }

            public function handle($request, $next)
            {
                return $next($request);
            }

            public function terminate($request, $response)
            {
                $this->called[] = 'terminating middleware';
            }
        });
        $kernel->setGlobalMiddleware([
            'terminating-middleware',
        ]);
        $events->listen(function (Terminating $terminating) use (&$called) {
            $called[] = 'terminating event';
        });
        $app->terminating(function () use (&$called) {
            $called[] = 'terminating callback';
        });

        $kernel->terminate(new Request(), new Response());

        $this->assertSame([
            'terminating event',
            'terminating middleware',
            'terminating callback',
        ], $called);
    }

    /**
     * @return \Kasi\Contracts\Foundation\Application
     */
    protected function getApplication()
    {
        return new Application;
    }

    /**
     * @return \Kasi\Routing\Router
     */
    protected function getRouter()
    {
        return new Router(new Dispatcher);
    }
}
