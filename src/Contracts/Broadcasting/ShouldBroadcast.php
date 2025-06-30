<?php

namespace Kasi\Contracts\Broadcasting;

interface ShouldBroadcast
{
    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Kasi\Broadcasting\Channel|\Kasi\Broadcasting\Channel[]|string[]|string
     */
    public function broadcastOn();
}
