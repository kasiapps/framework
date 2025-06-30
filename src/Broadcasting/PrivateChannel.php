<?php

namespace Kasi\Broadcasting;

use Kasi\Contracts\Broadcasting\HasBroadcastChannel;

class PrivateChannel extends Channel
{
    /**
     * Create a new channel instance.
     *
     * @param  \Kasi\Contracts\Broadcasting\HasBroadcastChannel|string  $name
     * @return void
     */
    public function __construct($name)
    {
        $name = $name instanceof HasBroadcastChannel ? $name->broadcastChannel() : $name;

        parent::__construct('private-'.$name);
    }
}
