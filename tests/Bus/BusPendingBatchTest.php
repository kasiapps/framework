<?php

namespace Kasi\Tests\Bus;

use Kasi\Bus\Batch;
use Kasi\Bus\Batchable;
use Kasi\Bus\BatchRepository;
use Kasi\Bus\PendingBatch;
use Kasi\Container\Container;
use Kasi\Contracts\Events\Dispatcher;
use Kasi\Support\Collection;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use stdClass;

class BusPendingBatchTest extends TestCase
{
    protected function tearDown(): void
    {
        m::close();
    }

    public function test_pending_batch_may_be_configured_and_dispatched()
    {
        $container = new Container;

        $eventDispatcher = m::mock(Dispatcher::class);
        $eventDispatcher->shouldReceive('dispatch')->once();

        $container->instance(Dispatcher::class, $eventDispatcher);

        $job = new class
        {
            use Batchable;
        };

        $pendingBatch = new PendingBatch($container, new Collection([$job]));

        $pendingBatch = $pendingBatch->before(function () {
            //
        })->progress(function () {
            //
        })->then(function () {
            //
        })->catch(function () {
            //
        })->allowFailures()->onConnection('test-connection')->onQueue('test-queue')->withOption('extra-option', 123);

        $this->assertSame('test-connection', $pendingBatch->connection());
        $this->assertSame('test-queue', $pendingBatch->queue());
        $this->assertCount(1, $pendingBatch->beforeCallbacks());
        $this->assertCount(1, $pendingBatch->progressCallbacks());
        $this->assertCount(1, $pendingBatch->thenCallbacks());
        $this->assertCount(1, $pendingBatch->catchCallbacks());
        $this->assertArrayHasKey('extra-option', $pendingBatch->options);
        $this->assertSame(123, $pendingBatch->options['extra-option']);

        $repository = m::mock(BatchRepository::class);
        $repository->shouldReceive('store')->once()->with($pendingBatch)->andReturn($batch = m::mock(stdClass::class));
        $batch->shouldReceive('add')->once()->with(m::type(Collection::class))->andReturn($batch = m::mock(Batch::class));

        $container->instance(BatchRepository::class, $repository);

        $pendingBatch->dispatch();
    }

    public function test_batch_is_deleted_from_storage_if_exception_thrown_during_batching()
    {
        $this->expectException(RuntimeException::class);

        $container = new Container;

        $job = new class {};

        $pendingBatch = new PendingBatch($container, new Collection([$job]));

        $repository = m::mock(BatchRepository::class);

        $repository->shouldReceive('store')->once()->with($pendingBatch)->andReturn($batch = m::mock(stdClass::class));

        $batch->id = 'test-id';

        $batch->shouldReceive('add')->once()->andReturnUsing(function () {
            throw new RuntimeException('Failed to add jobs...');
        });

        $repository->shouldReceive('delete')->once()->with('test-id');

        $container->instance(BatchRepository::class, $repository);

        $pendingBatch->dispatch();
    }

    public function test_batch_is_dispatched_when_dispatchif_is_true()
    {
        $container = new Container;

        $eventDispatcher = m::mock(Dispatcher::class);
        $eventDispatcher->shouldReceive('dispatch')->once();
        $container->instance(Dispatcher::class, $eventDispatcher);

        $job = new class
        {
            use Batchable;
        };

        $pendingBatch = new PendingBatch($container, new Collection([$job]));

        $repository = m::mock(BatchRepository::class);
        $repository->shouldReceive('store')->once()->andReturn($batch = m::mock(stdClass::class));
        $batch->shouldReceive('add')->once()->andReturn($batch = m::mock(Batch::class));

        $container->instance(BatchRepository::class, $repository);

        $result = $pendingBatch->dispatchIf(true);

        $this->assertInstanceOf(Batch::class, $result);
    }

    public function test_batch_is_not_dispatched_when_dispatchif_is_false()
    {
        $container = new Container;

        $eventDispatcher = m::mock(Dispatcher::class);
        $eventDispatcher->shouldNotReceive('dispatch');
        $container->instance(Dispatcher::class, $eventDispatcher);

        $job = new class
        {
            use Batchable;
        };

        $pendingBatch = new PendingBatch($container, new Collection([$job]));

        $repository = m::mock(BatchRepository::class);
        $container->instance(BatchRepository::class, $repository);

        $result = $pendingBatch->dispatchIf(false);

        $this->assertNull($result);
    }

    public function test_batch_is_dispatched_when_dispatchunless_is_false()
    {
        $container = new Container;

        $eventDispatcher = m::mock(Dispatcher::class);
        $eventDispatcher->shouldReceive('dispatch')->once();
        $container->instance(Dispatcher::class, $eventDispatcher);

        $job = new class
        {
            use Batchable;
        };

        $pendingBatch = new PendingBatch($container, new Collection([$job]));

        $repository = m::mock(BatchRepository::class);
        $repository->shouldReceive('store')->once()->andReturn($batch = m::mock(stdClass::class));
        $batch->shouldReceive('add')->once()->andReturn($batch = m::mock(Batch::class));

        $container->instance(BatchRepository::class, $repository);

        $result = $pendingBatch->dispatchUnless(false);

        $this->assertInstanceOf(Batch::class, $result);
    }

    public function test_batch_is_not_dispatched_when_dispatchunless_is_true()
    {
        $container = new Container;

        $eventDispatcher = m::mock(Dispatcher::class);
        $eventDispatcher->shouldNotReceive('dispatch');
        $container->instance(Dispatcher::class, $eventDispatcher);

        $job = new class
        {
            use Batchable;
        };

        $pendingBatch = new PendingBatch($container, new Collection([$job]));

        $repository = m::mock(BatchRepository::class);
        $container->instance(BatchRepository::class, $repository);

        $result = $pendingBatch->dispatchUnless(true);

        $this->assertNull($result);
    }

    public function test_batch_before_event_is_called()
    {
        $container = new Container;

        $eventDispatcher = m::mock(Dispatcher::class);
        $eventDispatcher->shouldReceive('dispatch')->once();

        $container->instance(Dispatcher::class, $eventDispatcher);

        $job = new class
        {
            use Batchable;
        };

        $beforeCalled = false;

        $pendingBatch = new PendingBatch($container, new Collection([$job]));

        $pendingBatch = $pendingBatch->before(function () use (&$beforeCalled) {
            $beforeCalled = true;
        })->onConnection('test-connection')->onQueue('test-queue');

        $repository = m::mock(BatchRepository::class);
        $repository->shouldReceive('store')->once()->with($pendingBatch)->andReturn($batch = m::mock(stdClass::class));
        $batch->shouldReceive('add')->once()->with(m::type(Collection::class))->andReturn($batch = m::mock(Batch::class));

        $container->instance(BatchRepository::class, $repository);

        $pendingBatch->dispatch();

        $this->assertTrue($beforeCalled);
    }

    public function test_it_throws_exception_if_batched_job_is_not_batchable(): void
    {
        $nonBatchableJob = new class {};

        $this->expectException(RuntimeException::class);

        new PendingBatch(new Container, new Collection([$nonBatchableJob]));
    }

    public function test_it_throws_an_exception_if_batched_job_contains_batch_with_nonbatchable_job(): void
    {
        $this->expectException(RuntimeException::class);

        $container = new Container;
        new PendingBatch(
            $container,
            new Collection(
                [new PendingBatch($container, new Collection([new BatchableJob, new class {}]))]
            )
        );
    }

    public function test_it_can_batch_a_closure(): void
    {
        new PendingBatch(
            new Container,
            new Collection([
                function () {
                },
            ])
        );
        $this->expectNotToPerformAssertions();
    }
}

class BatchableJob
{
    use Batchable;
}
