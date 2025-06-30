<?php

namespace Kasi\Support;

use Kasi\Support\Defer\DeferredCallback;
use Kasi\Support\Defer\DeferredCallbackCollection;
use Kasi\Support\Process\PhpExecutableFinder;

if (! function_exists('Kasi\Support\defer')) {
    /**
     * Defer execution of the given callback.
     *
     * @param  callable|null  $callback
     * @param  string|null  $name
     * @param  bool  $always
     * @return \Kasi\Support\Defer\DeferredCallback
     */
    function defer(?callable $callback = null, ?string $name = null, bool $always = false)
    {
        if ($callback === null) {
            return app(DeferredCallbackCollection::class);
        }

        return tap(
            new DeferredCallback($callback, $name, $always),
            fn ($deferred) => app(DeferredCallbackCollection::class)[] = $deferred
        );
    }
}

if (! function_exists('Kasi\Support\php_binary')) {
    /**
     * Determine the PHP Binary.
     *
     * @return string
     */
    function php_binary()
    {
        return (new PhpExecutableFinder)->find(false) ?: 'php';
    }
}

if (! function_exists('Kasi\Support\artisan_binary')) {
    /**
     * Determine the proper Artisan executable.
     *
     * @return string
     */
    function artisan_binary()
    {
        return defined('ARTISAN_BINARY') ? ARTISAN_BINARY : 'artisan';
    }
}
