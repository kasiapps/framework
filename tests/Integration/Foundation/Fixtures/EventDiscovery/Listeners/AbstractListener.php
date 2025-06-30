<?php

namespace Kasi\Tests\Integration\Foundation\Fixtures\EventDiscovery\Listeners;

use Kasi\Tests\Integration\Foundation\Fixtures\EventDiscovery\Events\EventOne;

abstract class AbstractListener
{
    abstract public function handle(EventOne $event);
}
