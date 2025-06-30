<?php

namespace Kasi\Support\Facades;

use Closure;
use Kasi\Process\Factory;

/**
 * @method static \Kasi\Process\PendingProcess command(array|string $command)
 * @method static \Kasi\Process\PendingProcess path(string $path)
 * @method static \Kasi\Process\PendingProcess timeout(int $timeout)
 * @method static \Kasi\Process\PendingProcess idleTimeout(int $timeout)
 * @method static \Kasi\Process\PendingProcess forever()
 * @method static \Kasi\Process\PendingProcess env(array $environment)
 * @method static \Kasi\Process\PendingProcess input(\Traversable|resource|string|int|float|bool|null $input)
 * @method static \Kasi\Process\PendingProcess quietly()
 * @method static \Kasi\Process\PendingProcess tty(bool $tty = true)
 * @method static \Kasi\Process\PendingProcess options(array $options)
 * @method static \Kasi\Contracts\Process\ProcessResult run(array|string|null $command = null, callable|null $output = null)
 * @method static \Kasi\Process\InvokedProcess start(array|string|null $command = null, callable|null $output = null)
 * @method static bool supportsTty()
 * @method static \Kasi\Process\PendingProcess withFakeHandlers(array $fakeHandlers)
 * @method static \Kasi\Process\PendingProcess|mixed when(\Closure|mixed|null $value = null, callable|null $callback = null, callable|null $default = null)
 * @method static \Kasi\Process\PendingProcess|mixed unless(\Closure|mixed|null $value = null, callable|null $callback = null, callable|null $default = null)
 * @method static \Kasi\Process\FakeProcessResult result(array|string $output = '', array|string $errorOutput = '', int $exitCode = 0)
 * @method static \Kasi\Process\FakeProcessDescription describe()
 * @method static \Kasi\Process\FakeProcessSequence sequence(array $processes = [])
 * @method static bool isRecording()
 * @method static \Kasi\Process\Factory recordIfRecording(\Kasi\Process\PendingProcess $process, \Kasi\Contracts\Process\ProcessResult $result)
 * @method static \Kasi\Process\Factory record(\Kasi\Process\PendingProcess $process, \Kasi\Contracts\Process\ProcessResult $result)
 * @method static \Kasi\Process\Factory preventStrayProcesses(bool $prevent = true)
 * @method static bool preventingStrayProcesses()
 * @method static \Kasi\Process\Factory assertRan(\Closure|string $callback)
 * @method static \Kasi\Process\Factory assertRanTimes(\Closure|string $callback, int $times = 1)
 * @method static \Kasi\Process\Factory assertNotRan(\Closure|string $callback)
 * @method static \Kasi\Process\Factory assertDidntRun(\Closure|string $callback)
 * @method static \Kasi\Process\Factory assertNothingRan()
 * @method static \Kasi\Process\Pool pool(callable $callback)
 * @method static \Kasi\Contracts\Process\ProcessResult pipe(callable|array $callback, callable|null $output = null)
 * @method static \Kasi\Process\ProcessPoolResults concurrently(callable $callback, callable|null $output = null)
 * @method static \Kasi\Process\PendingProcess newPendingProcess()
 * @method static void macro(string $name, object|callable $macro)
 * @method static void mixin(object $mixin, bool $replace = true)
 * @method static bool hasMacro(string $name)
 * @method static void flushMacros()
 * @method static mixed macroCall(string $method, array $parameters)
 *
 * @see \Kasi\Process\PendingProcess
 * @see \Kasi\Process\Factory
 */
class Process extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return Factory::class;
    }

    /**
     * Indicate that the process factory should fake processes.
     *
     * @param  \Closure|array|null  $callback
     * @return \Kasi\Process\Factory
     */
    public static function fake(Closure|array|null $callback = null)
    {
        return tap(static::getFacadeRoot(), function ($fake) use ($callback) {
            static::swap($fake->fake($callback));
        });
    }
}
