<?php

namespace Kasi\Container\Attributes;

use Attribute;
use Kasi\Contracts\Container\Container;
use Kasi\Contracts\Container\ContextualAttribute;

#[Attribute(Attribute::TARGET_PARAMETER)]
class Cache implements ContextualAttribute
{
    /**
     * Create a new class instance.
     */
    public function __construct(public ?string $store = null)
    {
    }

    /**
     * Resolve the cache store.
     *
     * @param  self  $attribute
     * @param  \Kasi\Contracts\Container\Container  $container
     * @return \Kasi\Contracts\Cache\Repository
     */
    public static function resolve(self $attribute, Container $container)
    {
        return $container->make('cache')->store($attribute->store);
    }
}
