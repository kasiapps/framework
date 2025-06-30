<?php

namespace Kasi\Tests\Integration\Http\Fixtures;

use Kasi\Http\Resources\Json\ResourceCollection;

class EmptyPostCollectionResource extends ResourceCollection
{
    public $collects = PostResource::class;
}
