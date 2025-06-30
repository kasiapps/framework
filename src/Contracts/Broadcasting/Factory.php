<?php

namespace Kasi\Contracts\Broadcasting;

interface Factory
{
    /**
     * Get a broadcaster implementation by name.
     *
     * @param  string|null  $name
     * @return \Kasi\Contracts\Broadcasting\Broadcaster
     */
    public function connection($name = null);
}
