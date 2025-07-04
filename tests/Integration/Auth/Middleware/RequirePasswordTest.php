<?php

namespace Kasi\Tests\Integration\Auth\Middleware;

use Kasi\Auth\Middleware\RequirePassword;
use Kasi\Contracts\Config\Repository;
use Kasi\Contracts\Routing\Registrar;
use Kasi\Contracts\Routing\UrlGenerator;
use Kasi\Http\Response;
use Kasi\Session\Middleware\StartSession;
use Orchestra\Testbench\TestCase;

class RequirePasswordTest extends TestCase
{
    public function testItCanGenerateDefinitionViaStaticMethod()
    {
        $signature = (string) RequirePassword::using('route.name');
        $this->assertSame('Kasi\Auth\Middleware\RequirePassword:route.name', $signature);

        $signature = (string) RequirePassword::using('route.name', 100);
        $this->assertSame('Kasi\Auth\Middleware\RequirePassword:route.name,100', $signature);

        $signature = (string) RequirePassword::using(passwordTimeoutSeconds: 100);
        $this->assertSame('Kasi\Auth\Middleware\RequirePassword:,100', $signature);
    }

    public function testUserSeesTheWantedPageIfThePasswordWasRecentlyConfirmed()
    {
        $this->withoutExceptionHandling();

        /** @var \Kasi\Contracts\Routing\Registrar $router */
        $router = $this->app->make(Registrar::class);

        $router->get('test-route', function (): Response {
            return new Response('foobar');
        })->middleware([StartSession::class, RequirePassword::class]);

        $response = $this->withSession(['auth.password_confirmed_at' => time()])->get('test-route');

        $response->assertOk();
        $response->assertSeeText('foobar');
    }

    public function testUserIsRedirectedToThePasswordConfirmRouteIfThePasswordWasNotRecentlyConfirmed()
    {
        $this->withoutExceptionHandling();

        /** @var \Kasi\Contracts\Routing\Registrar $router */
        $router = $this->app->make(Registrar::class);

        $router->get('password-confirm', function (): Response {
            return new Response('foo');
        })->name('password.confirm');

        $router->get('test-route', function (): Response {
            return new Response('foobar');
        })->middleware([StartSession::class, RequirePassword::class]);

        $response = $this->withSession(['auth.password_confirmed_at' => time() - 10801])->get('test-route');

        $response->assertStatus(302);
        $response->assertRedirect($this->app->make(UrlGenerator::class)->route('password.confirm'));
    }

    public function testUserIsRedirectedToACustomRouteIfThePasswordWasNotRecentlyConfirmedAndTheCustomRouteIsSpecified()
    {
        $this->withoutExceptionHandling();

        /** @var \Kasi\Contracts\Routing\Registrar $router */
        $router = $this->app->make(Registrar::class);

        $router->get('confirm', function (): Response {
            return new Response('foo');
        })->name('my-password.confirm');

        $router->get('test-route', function (): Response {
            return new Response('foobar');
        })->middleware([StartSession::class, RequirePassword::class.':my-password.confirm']);

        $response = $this->withSession(['auth.password_confirmed_at' => time() - 10801])->get('test-route');

        $response->assertStatus(302);
        $response->assertRedirect($this->app->make(UrlGenerator::class)->route('my-password.confirm'));
    }

    public function testAuthPasswordTimeoutIsConfigurable()
    {
        $this->withoutExceptionHandling();

        /** @var \Kasi\Contracts\Routing\Registrar $router */
        $router = $this->app->make(Registrar::class);

        $router->get('password-confirm', function (): Response {
            return new Response('foo');
        })->name('password.confirm');

        $router->get('test-route', function (): Response {
            return new Response('foobar');
        })->middleware([StartSession::class, RequirePassword::class]);

        $this->app->make(Repository::class)->set('auth.password_timeout', 500);

        $response = $this->withSession(['auth.password_confirmed_at' => time() - 495])->get('test-route');

        $response->assertOk();
        $response->assertSeeText('foobar');

        $response = $this->withSession(['auth.password_confirmed_at' => time() - 501])->get('test-route');

        $response->assertStatus(302);
        $response->assertRedirect($this->app->make(UrlGenerator::class)->route('password.confirm'));
    }
}
