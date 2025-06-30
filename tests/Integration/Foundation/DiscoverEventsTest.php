<?php

namespace Kasi\Tests\Integration\Foundation;

use Kasi\Foundation\Events\DiscoverEvents;
use Kasi\Support\Stringable;
use Kasi\Tests\Integration\Foundation\Fixtures\EventDiscovery\Events\EventOne;
use Kasi\Tests\Integration\Foundation\Fixtures\EventDiscovery\Events\EventTwo;
use Kasi\Tests\Integration\Foundation\Fixtures\EventDiscovery\Listeners\AbstractListener;
use Kasi\Tests\Integration\Foundation\Fixtures\EventDiscovery\Listeners\Listener;
use Kasi\Tests\Integration\Foundation\Fixtures\EventDiscovery\Listeners\ListenerInterface;
use Kasi\Tests\Integration\Foundation\Fixtures\EventDiscovery\UnionListeners\UnionListener;
use Orchestra\Testbench\TestCase;
use SplFileInfo;

class DiscoverEventsTest extends TestCase
{
    protected function tearDown(): void
    {
        DiscoverEvents::$guessClassNamesUsingCallback = null;

        parent::tearDown();
    }

    public function testEventsCanBeDiscovered()
    {
        class_alias(Listener::class, 'Tests\Integration\Foundation\Fixtures\EventDiscovery\Listeners\Listener');
        class_alias(AbstractListener::class, 'Tests\Integration\Foundation\Fixtures\EventDiscovery\Listeners\AbstractListener');
        class_alias(ListenerInterface::class, 'Tests\Integration\Foundation\Fixtures\EventDiscovery\Listeners\ListenerInterface');

        $events = DiscoverEvents::within(__DIR__.'/Fixtures/EventDiscovery/Listeners', getcwd());

        $this->assertEquals([
            EventOne::class => [
                Listener::class.'@handle',
                Listener::class.'@handleEventOne',
            ],
            EventTwo::class => [
                Listener::class.'@handleEventTwo',
            ],
        ], $events);
    }

    public function testUnionEventsCanBeDiscovered()
    {
        class_alias(UnionListener::class, 'Tests\Integration\Foundation\Fixtures\EventDiscovery\UnionListeners\UnionListener');

        $events = DiscoverEvents::within(__DIR__.'/Fixtures/EventDiscovery/UnionListeners', getcwd());

        $this->assertEquals([
            EventOne::class => [
                UnionListener::class.'@handle',
            ],
            EventTwo::class => [
                UnionListener::class.'@handle',
            ],
        ], $events);
    }

    public function testEventsCanBeDiscoveredUsingCustomClassNameGuessing()
    {
        DiscoverEvents::guessClassNamesUsing(function (SplFileInfo $file, $basePath) {
            return (new Stringable($file->getRealPath()))
                ->after($basePath.DIRECTORY_SEPARATOR)
                ->before('.php')
                ->replace(DIRECTORY_SEPARATOR, '\\')
                ->ucfirst()
                ->prepend('Kasi\\')
                ->toString();
        });

        $events = DiscoverEvents::within(__DIR__.'/Fixtures/EventDiscovery/Listeners', getcwd());

        $this->assertEquals([
            EventOne::class => [
                Listener::class.'@handle',
                Listener::class.'@handleEventOne',
            ],
            EventTwo::class => [
                Listener::class.'@handleEventTwo',
            ],
        ], $events);
    }
}
