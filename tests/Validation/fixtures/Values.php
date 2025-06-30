<?php

namespace Kasi\Tests\Validation\fixtures;

use Kasi\Contracts\Support\Arrayable;

class Values implements Arrayable
{
    public function toArray()
    {
        return [1, 2, 3, 4];
    }
}
