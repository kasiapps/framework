<?php

namespace Kasi\Tests\Integration\Foundation\Support\Providers;

use Kasi\Foundation\Application;
use Kasi\Foundation\Configuration\Exceptions;
use Kasi\Foundation\Configuration\Middleware;
use Kasi\Foundation\Support\Providers\RouteServiceProvider;
use Kasi\Support\Facades\Route;
use Kasi\Testing\Assert;
use Orchestra\Testbench\TestCase;

class RouteServiceProviderTest extends TestCase
{
    /**
     * Resolve application implementation.
     *
     * @return \Kasi\Foundation\Application
     */
    protected function resolveApplication()
    {
        return Application::configure(static::applicationBasePath())
            ->withProviders([
                AppRouteServiceProvider::class,
            ])
            ->withRouting(
                using: function () {
                    Route::get('login', fn () => 'Login')->name('login');
                }
            )
            ->withMiddleware(function (Middleware $middleware) {
                //
            })
            ->withExceptions(function (Exceptions $exceptions) {
                //
            })->create();
    }

    public function test_it_can_register_multiple_route_service_providers()
    {
        Assert::assertArraySubset([
            RouteServiceProvider::class => true,
            AppRouteServiceProvider::class => true,
        ], $this->app->getLoadedProviders());
    }

    public function test_it_can_uses_routes_registered_using_bootstrap_file()
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Login');
    }

    public function test_it_can_uses_routes_registered_using_configuration_file()
    {
        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Hello');
    }
}

class AppRouteServiceProvider extends RouteServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->routes(function () {
            Route::get('dashboard', fn () => 'Hello')->name('dashboard');
        });
    }
}
