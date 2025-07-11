<?php

namespace Kasi\Tests\Integration\Cookie;

use Kasi\Contracts\Debug\ExceptionHandler;
use Kasi\Http\Response;
use Kasi\Session\NullSessionHandler;
use Kasi\Support\Carbon;
use Kasi\Support\Facades\Route;
use Kasi\Support\Facades\Session;
use Kasi\Support\Str;
use Mockery as m;
use Orchestra\Testbench\TestCase;

class CookieTest extends TestCase
{
    public function test_cookie_is_sent_back_with_proper_expire_time_when_should_expire_on_close()
    {
        $this->app['config']->set('session.expire_on_close', true);

        Route::get('/', function () {
            return 'hello world';
        })->middleware('web');

        $response = $this->get('/');
        $this->assertCount(2, $response->headers->getCookies());
        $this->assertEquals(0, $response->headers->getCookies()[1]->getExpiresTime());
    }

    public function test_cookie_is_sent_back_with_proper_expire_time_with_respect_to_lifetime()
    {
        $this->app['config']->set('session.expire_on_close', false);
        $this->app['config']->set('session.lifetime', 1);

        Route::get('/', function () {
            return 'hello world';
        })->middleware('web');

        Carbon::setTestNow(Carbon::now());
        $response = $this->get('/');
        $this->assertCount(2, $response->headers->getCookies());
        $this->assertEquals(Carbon::now()->getTimestamp() + 60, $response->headers->getCookies()[1]->getExpiresTime());
    }

    protected function defineEnvironment($app)
    {
        $app->instance(
            ExceptionHandler::class,
            $handler = m::mock(ExceptionHandler::class)->shouldIgnoreMissing()
        );

        $handler->shouldReceive('render')->andReturn(new Response);

        $app['config']->set('app.key', Str::random(32));
        $app['config']->set('session.driver', 'fake-null');

        Session::extend('fake-null', function () {
            return new NullSessionHandler;
        });
    }
}
