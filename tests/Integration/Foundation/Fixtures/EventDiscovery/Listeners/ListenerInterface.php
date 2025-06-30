<?php

namespace Kasi\Tests\Integration\Foundation\Fixtures\EventDiscovery\Listeners;

use Kasi\Tests\Integration\Foundation\Fixtures\EventDiscovery\Events\EventOne;

interface ListenerInterface
{
    public function handle(EventOne $event);
}
