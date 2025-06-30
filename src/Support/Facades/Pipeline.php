<?php

namespace Kasi\Support\Facades;

/**
 * @method static \Kasi\Pipeline\Pipeline send(mixed $passable)
 * @method static \Kasi\Pipeline\Pipeline through(array|mixed $pipes)
 * @method static \Kasi\Pipeline\Pipeline pipe(array|mixed $pipes)
 * @method static \Kasi\Pipeline\Pipeline via(string $method)
 * @method static mixed then(\Closure $destination)
 * @method static mixed thenReturn()
 * @method static \Kasi\Pipeline\Pipeline finally(\Closure $callback)
 * @method static \Kasi\Pipeline\Pipeline setContainer(\Kasi\Contracts\Container\Container $container)
 * @method static \Kasi\Pipeline\Pipeline|mixed when(\Closure|mixed|null $value = null, callable|null $callback = null, callable|null $default = null)
 * @method static \Kasi\Pipeline\Pipeline|mixed unless(\Closure|mixed|null $value = null, callable|null $callback = null, callable|null $default = null)
 *
 * @see \Kasi\Pipeline\Pipeline
 */
class Pipeline extends Facade
{
    /**
     * Indicates if the resolved instance should be cached.
     *
     * @var bool
     */
    protected static $cached = false;

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'pipeline';
    }
}
