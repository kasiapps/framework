<?php

namespace Kasi\Events;

use Kasi\Support\Collection;

class InvokeQueuedClosure
{
    /**
     * Handle the event.
     *
     * @param  \Kasi\SerializableClosure\SerializableClosure  $closure
     * @param  array  $arguments
     * @return void
     */
    public function handle($closure, array $arguments)
    {
        call_user_func($closure->getClosure(), ...$arguments);
    }

    /**
     * Handle a job failure.
     *
     * @param  \Kasi\SerializableClosure\SerializableClosure  $closure
     * @param  array  $arguments
     * @param  array  $catchCallbacks
     * @param  \Throwable  $exception
     * @return void
     */
    public function failed($closure, array $arguments, array $catchCallbacks, $exception)
    {
        $arguments[] = $exception;

        (new Collection($catchCallbacks))->each->__invoke(...$arguments);
    }
}
