<?php

namespace Kasi\Support\Facades;

/**
 * @method static \Kasi\Hashing\BcryptHasher createBcryptDriver()
 * @method static \Kasi\Hashing\ArgonHasher createArgonDriver()
 * @method static \Kasi\Hashing\Argon2IdHasher createArgon2idDriver()
 * @method static array info(string $hashedValue)
 * @method static string make(string $value, array $options = [])
 * @method static bool check(string $value, string $hashedValue, array $options = [])
 * @method static bool needsRehash(string $hashedValue, array $options = [])
 * @method static bool isHashed(string $value)
 * @method static string getDefaultDriver()
 * @method static mixed driver(string|null $driver = null)
 * @method static \Kasi\Hashing\HashManager extend(string $driver, \Closure $callback)
 * @method static array getDrivers()
 * @method static \Kasi\Contracts\Container\Container getContainer()
 * @method static \Kasi\Hashing\HashManager setContainer(\Kasi\Contracts\Container\Container $container)
 * @method static \Kasi\Hashing\HashManager forgetDrivers()
 *
 * @see \Kasi\Hashing\HashManager
 * @see \Kasi\Hashing\AbstractHasher
 */
class Hash extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'hash';
    }
}
