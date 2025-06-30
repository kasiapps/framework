<?php

namespace Kasi\Tests\Queue;

use Kasi\Bus\Queueable;
use Kasi\Contracts\Queue\ShouldQueue;
use Kasi\Support\Facades\Queue;
use Orchestra\Testbench\TestCase;

class QueueDelayTest extends TestCase
{
    public function test_queue_delay()
    {
        Queue::fake();

        $job = new TestJob;

        dispatch($job);

        $this->assertEquals(60, $job->delay);
    }

    public function test_queue_without_delay()
    {
        Queue::fake();

        $job = new TestJob;

        dispatch($job->withoutDelay());

        $this->assertEquals(0, $job->delay);
    }

    public function test_pending_dispatch_without_delay()
    {
        Queue::fake();

        $job = new TestJob;

        dispatch($job)->withoutDelay();

        $this->assertEquals(0, $job->delay);
    }
}

class TestJob implements ShouldQueue
{
    use Queueable;

    public function __construct()
    {
        $this->delay(60);
    }
}
