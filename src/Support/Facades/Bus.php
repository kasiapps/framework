<?php

namespace Kasi\Support\Facades;

use Kasi\Bus\BatchRepository;
use Kasi\Contracts\Bus\Dispatcher as BusDispatcherContract;
use Kasi\Foundation\Bus\PendingChain;
use Kasi\Support\Testing\Fakes\BusFake;

/**
 * @method static mixed dispatch(mixed $command)
 * @method static mixed dispatchSync(mixed $command, mixed $handler = null)
 * @method static mixed dispatchNow(mixed $command, mixed $handler = null)
 * @method static \Kasi\Bus\Batch|null findBatch(string $batchId)
 * @method static \Kasi\Bus\PendingBatch batch(\Kasi\Support\Collection|array|mixed $jobs)
 * @method static \Kasi\Foundation\Bus\PendingChain chain(\Kasi\Support\Collection|array $jobs)
 * @method static bool hasCommandHandler(mixed $command)
 * @method static bool|mixed getCommandHandler(mixed $command)
 * @method static mixed dispatchToQueue(mixed $command)
 * @method static void dispatchAfterResponse(mixed $command, mixed $handler = null)
 * @method static \Kasi\Bus\Dispatcher pipeThrough(array $pipes)
 * @method static \Kasi\Bus\Dispatcher map(array $map)
 * @method static \Kasi\Support\Testing\Fakes\BusFake except(array|string $jobsToDispatch)
 * @method static void assertDispatched(string|\Closure $command, callable|int|null $callback = null)
 * @method static void assertDispatchedTimes(string|\Closure $command, int $times = 1)
 * @method static void assertNotDispatched(string|\Closure $command, callable|null $callback = null)
 * @method static void assertNothingDispatched()
 * @method static void assertDispatchedSync(string|\Closure $command, callable|int|null $callback = null)
 * @method static void assertDispatchedSyncTimes(string|\Closure $command, int $times = 1)
 * @method static void assertNotDispatchedSync(string|\Closure $command, callable|null $callback = null)
 * @method static void assertDispatchedAfterResponse(string|\Closure $command, callable|int|null $callback = null)
 * @method static void assertDispatchedAfterResponseTimes(string|\Closure $command, int $times = 1)
 * @method static void assertNotDispatchedAfterResponse(string|\Closure $command, callable|null $callback = null)
 * @method static void assertChained(array $expectedChain)
 * @method static void assertNothingChained()
 * @method static void assertDispatchedWithoutChain(string|\Closure $command, callable|null $callback = null)
 * @method static \Kasi\Support\Testing\Fakes\ChainedBatchTruthTest chainedBatch(\Closure $callback)
 * @method static void assertBatched(callable $callback)
 * @method static void assertBatchCount(int $count)
 * @method static void assertNothingBatched()
 * @method static void assertNothingPlaced()
 * @method static \Kasi\Support\Collection dispatched(string $command, callable|null $callback = null)
 * @method static \Kasi\Support\Collection dispatchedSync(string $command, callable|null $callback = null)
 * @method static \Kasi\Support\Collection dispatchedAfterResponse(string $command, callable|null $callback = null)
 * @method static \Kasi\Support\Collection batched(callable $callback)
 * @method static bool hasDispatched(string $command)
 * @method static bool hasDispatchedSync(string $command)
 * @method static bool hasDispatchedAfterResponse(string $command)
 * @method static \Kasi\Bus\Batch dispatchFakeBatch(string $name = '')
 * @method static \Kasi\Bus\Batch recordPendingBatch(\Kasi\Bus\PendingBatch $pendingBatch)
 * @method static \Kasi\Support\Testing\Fakes\BusFake serializeAndRestore(bool $serializeAndRestore = true)
 * @method static array dispatchedBatches()
 *
 * @see \Kasi\Bus\Dispatcher
 * @see \Kasi\Support\Testing\Fakes\BusFake
 */
class Bus extends Facade
{
    /**
     * Replace the bound instance with a fake.
     *
     * @param  array|string  $jobsToFake
     * @param  \Kasi\Bus\BatchRepository|null  $batchRepository
     * @return \Kasi\Support\Testing\Fakes\BusFake
     */
    public static function fake($jobsToFake = [], ?BatchRepository $batchRepository = null)
    {
        $actualDispatcher = static::isFake()
                ? static::getFacadeRoot()->dispatcher
                : static::getFacadeRoot();

        return tap(new BusFake($actualDispatcher, $jobsToFake, $batchRepository), function ($fake) {
            static::swap($fake);
        });
    }

    /**
     * Dispatch the given chain of jobs.
     *
     * @param  array|mixed  $jobs
     * @return \Kasi\Foundation\Bus\PendingDispatch
     */
    public static function dispatchChain($jobs)
    {
        $jobs = is_array($jobs) ? $jobs : func_get_args();

        return (new PendingChain(array_shift($jobs), $jobs))
            ->dispatch();
    }

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return BusDispatcherContract::class;
    }
}
