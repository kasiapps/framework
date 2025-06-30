<?php

namespace Kasi\Contracts\Redis;

interface Factory
{
    /**
     * Get a Redis connection by name.
     *
     * @param  string|null  $name
     * @return \Kasi\Redis\Connections\Connection
     */
    public function connection($name = null);
}
