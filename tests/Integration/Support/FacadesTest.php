<?php

namespace Kasi\Tests\Integration\Support;

use Kasi\Auth\AuthManager;
use Kasi\Foundation\Application;
use Kasi\Support\Collection;
use Kasi\Support\Facades\Auth;
use Kasi\Support\Facades\Facade;
use Orchestra\Testbench\TestCase;
use ReflectionClass;

class FacadesTest extends TestCase
{
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($_SERVER['__kasi.authResolved']);
    }

    public function testFacadeResolvedCanResolveCallback()
    {
        Auth::resolved(function (AuthManager $auth, Application $app) {
            $_SERVER['__kasi.authResolved'] = true;
        });

        $this->assertFalse(isset($_SERVER['__kasi.authResolved']));

        $this->app->make('auth');

        $this->assertTrue(isset($_SERVER['__kasi.authResolved']));
    }

    public function testFacadeResolvedCanResolveCallbackAfterAccessRootHasBeenResolved()
    {
        $this->app->make('auth');

        $this->assertFalse(isset($_SERVER['__kasi.authResolved']));

        Auth::resolved(function (AuthManager $auth, Application $app) {
            $_SERVER['__kasi.authResolved'] = true;
        });

        $this->assertTrue(isset($_SERVER['__kasi.authResolved']));
    }

    public function testDefaultAliases()
    {
        $defaultAliases = Facade::defaultAliases();

        $this->assertInstanceOf(Collection::class, $defaultAliases);

        foreach ($defaultAliases as $alias => $abstract) {
            $this->assertTrue(class_exists($alias));
            $this->assertTrue(class_exists($abstract));

            $reflection = new ReflectionClass($alias);
            $this->assertSame($abstract, $reflection->getName());
        }
    }
}
