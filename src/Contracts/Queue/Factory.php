<?php

namespace Kasi\Contracts\Queue;

interface Factory
{
    /**
     * Resolve a queue connection instance.
     *
     * @param  string|null  $name
     * @return \Kasi\Contracts\Queue\Queue
     */
    public function connection($name = null);
}
