<?php

namespace Kasi\Console\Events;

class ArtisanStarting
{
    /**
     * The Artisan application instance.
     *
     * @var \Kasi\Console\Application
     */
    public $artisan;

    /**
     * Create a new event instance.
     *
     * @param  \Kasi\Console\Application  $artisan
     * @return void
     */
    public function __construct($artisan)
    {
        $this->artisan = $artisan;
    }
}
