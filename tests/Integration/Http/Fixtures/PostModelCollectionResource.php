<?php

namespace Kasi\Tests\Integration\Http\Fixtures;

use Kasi\Http\Resources\Json\ResourceCollection;

class PostModelCollectionResource extends ResourceCollection
{
    public $collects = Post::class;

    public function toArray($request)
    {
        return ['data' => $this->collection];
    }
}
