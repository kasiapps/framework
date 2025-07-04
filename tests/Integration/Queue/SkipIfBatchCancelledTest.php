<?php

namespace Kasi\Tests\Integration\Queue;

use Kasi\Bus\Batchable;
use Kasi\Bus\Dispatcher;
use Kasi\Bus\Queueable;
use Kasi\Contracts\Queue\Job;
use Kasi\Queue\CallQueuedHandler;
use Kasi\Queue\InteractsWithQueue;
use Kasi\Queue\Middleware\SkipIfBatchCancelled;
use Mockery as m;
use Orchestra\Testbench\TestCase;

class SkipIfBatchCancelledTest extends TestCase
{
    public function testJobsAreSkippedOnceBatchIsCancelled()
    {
        [$beforeCancelled] = (new SkipCancelledBatchableTestJob())->withFakeBatch();
        [$afterCancelled] = (new SkipCancelledBatchableTestJob())->withFakeBatch(
            cancelledAt: \Carbon\CarbonImmutable::now()
        );

        $this->assertJobRanSuccessfully($beforeCancelled);
        $this->assertJobWasSkipped($afterCancelled);
    }

    protected function assertJobRanSuccessfully($class)
    {
        $this->assertJobHandled($class, true);
    }

    protected function assertJobWasSkipped($class)
    {
        $this->assertJobHandled($class, false);
    }

    protected function assertJobHandled($class, $expectedHandledValue)
    {
        $class::$handled = false;
        $instance = new CallQueuedHandler(new Dispatcher($this->app), $this->app);

        $job = m::mock(Job::class);

        $job->shouldReceive('uuid')->once()->andReturn('simple-test-uuid');
        $job->shouldReceive('hasFailed')->once()->andReturn(false);
        $job->shouldReceive('isReleased')->andReturn(false);
        $job->shouldReceive('isDeletedOrReleased')->once()->andReturn(false);
        $job->shouldReceive('delete')->once();

        $instance->call($job, [
            'command' => serialize($command = $class),
        ]);

        $this->assertEquals($expectedHandledValue, $class::$handled);
    }
}

class SkipCancelledBatchableTestJob
{
    use Batchable, InteractsWithQueue, Queueable;

    public static $handled = false;

    public function handle()
    {
        static::$handled = true;
    }

    public function middleware()
    {
        return [new SkipIfBatchCancelled];
    }
}
