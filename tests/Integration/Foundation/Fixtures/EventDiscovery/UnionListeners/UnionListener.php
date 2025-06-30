<?php

namespace Kasi\Tests\Integration\Foundation\Fixtures\EventDiscovery\UnionListeners;

use Kasi\Tests\Integration\Foundation\Fixtures\EventDiscovery\Events\EventOne;
use Kasi\Tests\Integration\Foundation\Fixtures\EventDiscovery\Events\EventTwo;

class UnionListener
{
    public function handle(EventOne|EventTwo $event)
    {
        //
    }
}
