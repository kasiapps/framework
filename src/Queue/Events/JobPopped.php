<?php

namespace Kasi\Queue\Events;

class JobPopped
{
    /**
     * The connection name.
     *
     * @var string
     */
    public $connectionName;

    /**
     * The job instance.
     *
     * @var \Kasi\Contracts\Queue\Job|null
     */
    public $job;

    /**
     * Create a new event instance.
     *
     * @param  string  $connectionName
     * @param  \Kasi\Contracts\Queue\Job|null  $job
     * @return void
     */
    public function __construct($connectionName, $job)
    {
        $this->connectionName = $connectionName;
        $this->job = $job;
    }
}
