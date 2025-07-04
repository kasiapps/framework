<?php

namespace Kasi\Tests\Integration\Http\Fixtures;

use Kasi\Http\Resources\Json\JsonResource;

class PostResourceWithAnonymousResourceCollectionWithPaginationInformation extends JsonResource
{
    public function toArray($request)
    {
        return ['id' => $this->id, 'title' => $this->title, 'custom' => true];
    }

    /**
     * Create a new anonymous resource collection.
     *
     * @param  mixed  $resource
     * @return \Kasi\Tests\Integration\Http\Fixtures\AnonymousResourceCollectionWithPaginationInformation
     */
    public static function collection($resource)
    {
        return tap(new AnonymousResourceCollectionWithPaginationInformation($resource, static::class), function ($collection) {
            if (property_exists(static::class, 'preserveKeys')) {
                $collection->preserveKeys = (new static([]))->preserveKeys === true;
            }
        });
    }
}
