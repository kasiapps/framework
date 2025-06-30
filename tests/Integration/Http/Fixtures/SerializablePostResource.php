<?php

namespace Kasi\Tests\Integration\Http\Fixtures;

use Kasi\Http\Resources\Json\JsonResource;

class SerializablePostResource extends JsonResource
{
    public function toArray($request)
    {
        return new JsonSerializableResource($this);
    }
}
