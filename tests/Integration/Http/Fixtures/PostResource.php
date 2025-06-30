<?php

namespace Kasi\Tests\Integration\Http\Fixtures;

use Kasi\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
{
    public function toArray($request)
    {
        return ['id' => $this->id, 'title' => $this->title, 'custom' => true];
    }

    public function withResponse($request, $response)
    {
        $response->header('X-Resource', 'True');
    }
}
