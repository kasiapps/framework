<?php

namespace Kasi\Support\Facades;

use Kasi\Concurrency\ConcurrencyManager;

/**
 * @method static mixed driver(string|null $name = null)
 * @method static \Kasi\Concurrency\ProcessDriver createProcessDriver(array $config)
 * @method static \Kasi\Concurrency\ForkDriver createForkDriver(array $config)
 * @method static \Kasi\Concurrency\SyncDriver createSyncDriver(array $config)
 * @method static string getDefaultInstance()
 * @method static void setDefaultInstance(string $name)
 * @method static array getInstanceConfig(string $name)
 * @method static mixed instance(string|null $name = null)
 * @method static \Kasi\Concurrency\ConcurrencyManager forgetInstance(array|string|null $name = null)
 * @method static void purge(string|null $name = null)
 * @method static \Kasi\Concurrency\ConcurrencyManager extend(string $name, \Closure $callback)
 * @method static \Kasi\Concurrency\ConcurrencyManager setApplication(\Kasi\Contracts\Foundation\Application $app)
 * @method static array run(\Closure|array $tasks)
 * @method static \Kasi\Support\Defer\DeferredCallback defer(\Closure|array $tasks)
 *
 * @see \Kasi\Concurrency\ConcurrencyManager
 */
class Concurrency extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return ConcurrencyManager::class;
    }
}
