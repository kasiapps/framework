<?php

namespace Kasi\Tests\Integration\Cache\Fixtures;

use Exception;

class Unserializable
{
    public function __sleep()
    {
        throw new Exception('Not serializable');
    }
}
