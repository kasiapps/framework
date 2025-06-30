<?php

namespace Kasi\Tests\Testing;

use Kasi\Contracts\Routing\Registrar;
use Kasi\Http\RedirectResponse;
use Kasi\Routing\UrlGenerator;
use Kasi\Support\Facades\Facade;
use Orchestra\Testbench\TestCase;

class AssertRedirectToSignedRouteTest extends TestCase
{
    /**
     * @var \Kasi\Contracts\Routing\Registrar
     */
    private $router;

    /**
     * @var \Kasi\Routing\UrlGenerator
     */
    private $urlGenerator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->router = $this->app->make(Registrar::class);

        $this->router
            ->get('signed-route')
            ->name('signed-route');

        $this->router
            ->get('signed-route-with-param/{param}')
            ->name('signed-route-with-param');

        $this->urlGenerator = $this->app->make(UrlGenerator::class);
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set(['app.key' => 'AckfSECXIvnK5r28GVIWUAxmbBSjTsmF']);
    }

    public function testAssertRedirectToSignedRouteWithoutRouteName()
    {
        $this->router->get('test-route', function () {
            return new RedirectResponse($this->urlGenerator->signedRoute('signed-route'));
        });

        $this->get('test-route')
            ->assertRedirectToSignedRoute();
    }

    public function testAssertRedirectToSignedRouteWithRouteName()
    {
        $this->router->get('test-route', function () {
            return new RedirectResponse($this->urlGenerator->signedRoute('signed-route'));
        });

        $this->get('test-route')
            ->assertRedirectToSignedRoute('signed-route');
    }

    public function testAssertRedirectToSignedRouteWithRouteNameAndParams()
    {
        $this->router->get('test-route', function () {
            return new RedirectResponse($this->urlGenerator->signedRoute('signed-route-with-param', 'hello'));
        });

        $this->router->get('test-route-with-extra-param', function () {
            return new RedirectResponse($this->urlGenerator->signedRoute('signed-route-with-param', [
                'param' => 'foo',
                'extra' => 'another',
            ]));
        });

        $this->get('test-route')
            ->assertRedirectToSignedRoute('signed-route-with-param', 'hello');

        $this->get('test-route-with-extra-param')
            ->assertRedirectToSignedRoute('signed-route-with-param', [
                'param' => 'foo',
                'extra' => 'another',
            ]);
    }

    public function testAssertRedirectToSignedRouteWithRouteNameToTemporarySignedRoute()
    {
        $this->router->get('test-route', function () {
            return new RedirectResponse($this->urlGenerator->temporarySignedRoute('signed-route', 60));
        });

        $this->get('test-route')
            ->assertRedirectToSignedRoute('signed-route');
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        Facade::setFacadeApplication(null);
    }
}
