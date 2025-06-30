<?php

namespace Kasi\Tests\Support;

use Kasi\Cache\CacheManager;
use Kasi\Cache\Events\CacheMissed;
use Kasi\Cache\Events\RetrievingKey;
use Kasi\Config\Repository as ConfigRepository;
use Kasi\Container\Container;
use Kasi\Contracts\Events\Dispatcher as DispatcherContract;
use Kasi\Database\Eloquent\Model;
use Kasi\Events\Dispatcher;
use Kasi\Support\Facades\Cache;
use Kasi\Support\Facades\Event;
use Kasi\Support\Facades\Facade;
use Kasi\Support\Testing\Fakes\EventFake;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class SupportFacadesEventTest extends TestCase
{
    private $events;

    protected function setUp(): void
    {
        parent::setUp();

        $this->events = m::mock(Dispatcher::class);

        $container = new Container;
        $container->instance('events', $this->events);
        $container->alias('events', DispatcherContract::class);
        $container->instance('cache', new CacheManager($container));
        $container->instance('config', new ConfigRepository($this->getCacheConfig()));

        Facade::setFacadeApplication($container);
    }

    protected function tearDown(): void
    {
        Event::clearResolvedInstances();
        Event::setFacadeApplication(null);

        m::close();
    }

    public function testFakeFor()
    {
        Event::fakeFor(function () {
            (new FakeForStub)->dispatch();

            Event::assertDispatched(EventStub::class);
        });

        $this->events->shouldReceive('dispatch')->once();

        (new FakeForStub)->dispatch();
    }

    public function testFakeForSwapsDispatchers()
    {
        $arrayRepository = Cache::store('array');

        Event::fakeFor(function () use ($arrayRepository) {
            $this->assertInstanceOf(EventFake::class, Event::getFacadeRoot());
            $this->assertInstanceOf(EventFake::class, Model::getEventDispatcher());
            $this->assertInstanceOf(EventFake::class, $arrayRepository->getEventDispatcher());
        });

        $this->assertSame($this->events, Event::getFacadeRoot());
        $this->assertSame($this->events, Model::getEventDispatcher());
        $this->assertSame($this->events, $arrayRepository->getEventDispatcher());
    }

    public function testFakeSwapsDispatchersInResolvedCacheRepositories()
    {
        $arrayRepository = Cache::store('array');

        $this->events->shouldReceive('dispatch')->times(2);
        $arrayRepository->get('foo');

        Event::fake();

        $arrayRepository->get('bar');

        Event::assertDispatched(RetrievingKey::class);
        Event::assertDispatched(CacheMissed::class);
    }

    protected function getCacheConfig()
    {
        return [
            'cache' => [
                'stores' => [
                    'array' => [
                        'driver' => 'array',
                    ],
                ],
            ],
        ];
    }
}

class FakeForStub
{
    public function dispatch()
    {
        Event::dispatch(EventStub::class);
    }
}
