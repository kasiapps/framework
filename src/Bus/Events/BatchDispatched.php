<?php

namespace Kasi\Bus\Events;

use Kasi\Bus\Batch;

class BatchDispatched
{
    /**
     * The batch instance.
     *
     * @var \Kasi\Bus\Batch
     */
    public $batch;

    /**
     * Create a new event instance.
     *
     * @param  \Kasi\Bus\Batch  $batch
     * @return void
     */
    public function __construct(Batch $batch)
    {
        $this->batch = $batch;
    }
}
