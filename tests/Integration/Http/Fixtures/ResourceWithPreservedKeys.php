<?php

namespace Kasi\Tests\Integration\Http\Fixtures;

class ResourceWithPreservedKeys extends PostResource
{
    protected $preserveKeys = true;

    public function toArray($request)
    {
        return $this->resource;
    }
}
