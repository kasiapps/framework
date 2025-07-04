<?php

namespace Kasi\Tests\Queue;

use Exception;
use Kasi\Container\Container;
use Kasi\Contracts\Events\Dispatcher;
use Kasi\Contracts\Queue\QueueableEntity;
use Kasi\Contracts\Queue\ShouldQueue;
use Kasi\Contracts\Queue\ShouldQueueAfterCommit;
use Kasi\Database\DatabaseTransactionsManager;
use Kasi\Queue\InteractsWithQueue;
use Kasi\Queue\Jobs\SyncJob;
use Kasi\Queue\SyncQueue;
use LogicException;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class QueueSyncQueueTest extends TestCase
{
    protected function tearDown(): void
    {
        m::close();

        Container::setInstance(null);
    }

    public function testPushShouldFireJobInstantly()
    {
        unset($_SERVER['__sync.test']);

        $sync = new SyncQueue;
        $container = new Container;
        $sync->setContainer($container);

        $sync->push(SyncQueueTestHandler::class, ['foo' => 'bar']);
        $this->assertInstanceOf(SyncJob::class, $_SERVER['__sync.test'][0]);
        $this->assertEquals(['foo' => 'bar'], $_SERVER['__sync.test'][1]);
    }

    public function testFailedJobGetsHandledWhenAnExceptionIsThrown()
    {
        unset($_SERVER['__sync.failed']);

        $sync = new SyncQueue;
        $container = new Container;
        Container::setInstance($container);
        $events = m::mock(Dispatcher::class);
        $events->shouldReceive('dispatch')->times(3);
        $container->instance('events', $events);
        $container->instance(Dispatcher::class, $events);
        $sync->setContainer($container);

        try {
            $sync->push(FailingSyncQueueTestHandler::class, ['foo' => 'bar']);
        } catch (Exception) {
            $this->assertTrue($_SERVER['__sync.failed']);
        }

        Container::setInstance();
    }

    public function testCreatesPayloadObject()
    {
        $sync = new SyncQueue;
        $container = new Container;
        $container->bind(\Kasi\Contracts\Events\Dispatcher::class, \Kasi\Events\Dispatcher::class);
        $container->bind(\Kasi\Contracts\Bus\Dispatcher::class, \Kasi\Bus\Dispatcher::class);
        $container->bind(\Kasi\Contracts\Container\Container::class, \Kasi\Container\Container::class);
        $sync->setContainer($container);

        SyncQueue::createPayloadUsing(function ($connection, $queue, $payload) {
            return ['data' => ['extra' => 'extraValue']];
        });

        try {
            $sync->push(new SyncQueueJob());
        } catch (LogicException $e) {
            $this->assertSame('extraValue', $e->getMessage());
        }
    }

    public function testItAddsATransactionCallbackForAfterCommitJobs()
    {
        $sync = new SyncQueue;
        $container = new Container;
        $container->bind(\Kasi\Contracts\Container\Container::class, \Kasi\Container\Container::class);
        $transactionManager = m::mock(DatabaseTransactionsManager::class);
        $transactionManager->shouldReceive('addCallback')->once()->andReturn(null);
        $container->instance('db.transactions', $transactionManager);

        $sync->setContainer($container);
        $sync->push(new SyncQueueAfterCommitJob());
    }

    public function testItAddsATransactionCallbackForInterfaceBasedAfterCommitJobs()
    {
        $sync = new SyncQueue;
        $container = new Container;
        $container->bind(\Kasi\Contracts\Container\Container::class, \Kasi\Container\Container::class);
        $transactionManager = m::mock(DatabaseTransactionsManager::class);
        $transactionManager->shouldReceive('addCallback')->once()->andReturn(null);
        $container->instance('db.transactions', $transactionManager);

        $sync->setContainer($container);
        $sync->push(new SyncQueueAfterCommitInterfaceJob());
    }
}

class SyncQueueTestEntity implements QueueableEntity
{
    public function getQueueableId()
    {
        return 1;
    }

    public function getQueueableConnection()
    {
        //
    }

    public function getQueueableRelations()
    {
        //
    }
}

class SyncQueueTestHandler
{
    public function fire($job, $data)
    {
        $_SERVER['__sync.test'] = func_get_args();
    }
}

class FailingSyncQueueTestHandler
{
    public function fire($job, $data)
    {
        throw new Exception;
    }

    public function failed()
    {
        $_SERVER['__sync.failed'] = true;
    }
}

class SyncQueueJob implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle()
    {
        throw new LogicException($this->getValueFromJob('extra'));
    }

    public function getValueFromJob($key)
    {
        $payload = $this->job->payload();

        return $payload['data'][$key] ?? null;
    }
}

class SyncQueueAfterCommitJob
{
    use InteractsWithQueue;

    public $afterCommit = true;

    public function handle()
    {
    }
}

class SyncQueueAfterCommitInterfaceJob implements ShouldQueueAfterCommit
{
    use InteractsWithQueue;

    public function handle()
    {
    }
}
