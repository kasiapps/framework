<?php

namespace Kasi\Console\Events;

use Kasi\Console\Scheduling\Event;

class ScheduledTaskSkipped
{
    /**
     * The scheduled event being run.
     *
     * @var \Kasi\Console\Scheduling\Event
     */
    public $task;

    /**
     * Create a new event instance.
     *
     * @param  \Kasi\Console\Scheduling\Event  $task
     * @return void
     */
    public function __construct(Event $task)
    {
        $this->task = $task;
    }
}
