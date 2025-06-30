<?php

namespace Kasi\Tests\Integration\Foundation\Fixtures\EventDiscovery\Listeners;

use Kasi\Tests\Integration\Foundation\Fixtures\EventDiscovery\Events\EventOne;
use Kasi\Tests\Integration\Foundation\Fixtures\EventDiscovery\Events\EventTwo;

class Listener
{
    public function handle(EventOne $event)
    {
        //
    }

    public function handleEventOne(EventOne $event)
    {
        //
    }

    public function handleEventTwo(EventTwo $event)
    {
        //
    }
}
