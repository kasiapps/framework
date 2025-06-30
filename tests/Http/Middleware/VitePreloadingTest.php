<?php

namespace Kasi\Tests\Http\Middleware;

use Kasi\Container\Container;
use Kasi\Foundation\Vite;
use Kasi\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Kasi\Http\Request;
use Kasi\Http\Response;
use Kasi\Support\Facades\Facade;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class VitePreloadingTest extends TestCase
{
    protected function tearDown(): void
    {
        Facade::setFacadeApplication(null);
        Facade::clearResolvedInstances();
    }

    public function testItDoesNotSetLinkTagWhenNoTagsHaveBeenPreloaded()
    {
        $app = new Container;
        $app->instance(Vite::class, new class extends Vite
        {
            protected $preloadedAssets = [];
        });
        Facade::setFacadeApplication($app);

        $response = (new AddLinkHeadersForPreloadedAssets)->handle(new Request, function () {
            return new Response('Hello Kasi');
        });

        $this->assertNull($response->headers->get('Link'));
    }

    public function testItAddsPreloadLinkHeader()
    {
        $app = new Container;
        $app->instance(Vite::class, new class extends Vite
        {
            protected $preloadedAssets = [
                'https://kasi.com/app.js' => [
                    'rel="modulepreload"',
                    'foo="bar"',
                ],
            ];
        });
        Facade::setFacadeApplication($app);

        $response = (new AddLinkHeadersForPreloadedAssets)->handle(new Request, function () {
            return new Response('Hello Kasi');
        });

        $this->assertSame(
            '<https://kasi.com/app.js>; rel="modulepreload"; foo="bar"',
            $response->headers->get('Link'),
        );
    }

    public function testItDoesNotAttachHeadersToNonKasiResponses()
    {
        $app = new Container;
        $app->instance(Vite::class, new class extends Vite
        {
            protected $preloadedAssets = [
                'https://kasi.com/app.js' => [
                    'rel="modulepreload"',
                    'foo="bar"',
                ],
            ];
        });
        Facade::setFacadeApplication($app);

        $response = (new AddLinkHeadersForPreloadedAssets)->handle(new Request, function () {
            return new SymfonyResponse('Hello Kasi');
        });

        $this->assertNull($response->headers->get('Link'));
    }

    public function testItDoesNotOverwriteOtherLinkHeaders()
    {
        $app = new Container;
        $app->instance(Vite::class, new class extends Vite
        {
            protected $preloadedAssets = [
                'https://kasi.com/app.js' => [
                    'rel="modulepreload"',
                    'foo="bar"',
                ],
            ];
        });
        Facade::setFacadeApplication($app);

        $response = (new AddLinkHeadersForPreloadedAssets)->handle(new Request, function () {
            return new Response('Hello Kasi', headers: ['Link' => '<https://kasi.com/logo.png>; rel="preload"; as="image"']);
        });

        $this->assertSame(
            [
                '<https://kasi.com/logo.png>; rel="preload"; as="image"',
                '<https://kasi.com/app.js>; rel="modulepreload"; foo="bar"',
            ],
            $response->headers->all('Link'),
        );
    }
}
