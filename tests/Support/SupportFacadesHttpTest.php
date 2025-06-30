<?php

namespace Kasi\Tests\Support;

use Kasi\Container\Container;
use Kasi\Http\Client\Factory;
use Kasi\Support\Facades\Facade;
use Kasi\Support\Facades\Http;
use PHPUnit\Framework\TestCase;

class SupportFacadesHttpTest extends TestCase
{
    protected $app;

    protected function setUp(): void
    {
        $this->app = new Container;
        Facade::setFacadeApplication($this->app);
    }

    public function testFacadeRootIsNotSharedByDefault(): void
    {
        $this->assertNotSame(Http::getFacadeRoot(), $this->app->make(Factory::class));
    }

    public function testFacadeRootIsSharedWhenFaked(): void
    {
        Http::fake([
            'https://kasi.com' => Http::response('OK!'),
        ]);

        $factory = $this->app->make(Factory::class);
        $this->assertSame('OK!', $factory->get('https://kasi.com')->body());
    }

    public function testFacadeRootIsSharedWhenFakedWithSequence(): void
    {
        Http::fakeSequence('kasi.com/*')->push('OK!');

        $factory = $this->app->make(Factory::class);
        $this->assertSame('OK!', $factory->get('https://kasi.com')->body());
    }

    public function testFacadeRootIsSharedWhenStubbingUrls(): void
    {
        Http::stubUrl('kasi.com', Http::response('OK!'));

        $factory = $this->app->make(Factory::class);
        $this->assertSame('OK!', $factory->get('https://kasi.com')->body());
    }

    public function testFacadeRootIsSharedWhenEnforcingFaking(): void
    {
        $client = Http::preventStrayRequests();

        $this->assertSame($client, $this->app->make(Factory::class));
    }

    public function test_can_set_prevents_to_prevents_stray_requests(): void
    {
        Http::preventStrayRequests(true);
        $this->assertTrue($this->app->make(Factory::class)->preventingStrayRequests());
        $this->assertTrue(Http::preventingStrayRequests());

        Http::preventStrayRequests(false);
        $this->assertFalse($this->app->make(Factory::class)->preventingStrayRequests());
        $this->assertFalse(Http::preventingStrayRequests());
    }
}
