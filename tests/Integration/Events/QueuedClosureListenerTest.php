<?php

namespace Kasi\Tests\Integration\Events;

use Kasi\Events\CallQueuedListener;
use Kasi\Events\InvokeQueuedClosure;
use Kasi\Support\Facades\Bus;
use Kasi\Support\Facades\Event;
use Orchestra\Testbench\TestCase;

class QueuedClosureListenerTest extends TestCase
{
    public function testAnonymousQueuedListenerIsQueued()
    {
        Bus::fake();

        Event::listen(\Kasi\Events\queueable(function (TestEvent $event) {
            //
        })->catch(function (TestEvent $event) {
            //
        })->onConnection(null)->onQueue(null));

        Event::dispatch(new TestEvent);

        Bus::assertDispatched(CallQueuedListener::class, function ($job) {
            return $job->class == InvokeQueuedClosure::class;
        });
    }
}

class TestEvent
{
    //
}
