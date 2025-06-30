<?php

namespace Kasi\Tests\Integration\Foundation\Support\Providers;

use Kasi\Foundation\Application;
use Kasi\Support\Str;
use Orchestra\Testbench\TestCase;

class RouteServiceProviderHealthTest extends TestCase
{
    /**
     * Resolve application implementation.
     *
     * @return \Kasi\Foundation\Application
     */
    protected function resolveApplication()
    {
        return Application::configure(static::applicationBasePath())
            ->withRouting(
                web: __DIR__.'/fixtures/web.php',
                health: '/up',
            )->create();
    }

    protected function defineEnvironment($app)
    {
        $app['config']->set('app.key', Str::random(32));
    }

    public function test_it_can_load_health_page()
    {
        $this->get('/up')->assertOk();
    }
}
