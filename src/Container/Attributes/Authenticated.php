<?php

namespace Kasi\Container\Attributes;

use Attribute;
use Kasi\Contracts\Container\Container;
use Kasi\Contracts\Container\ContextualAttribute;

#[Attribute(Attribute::TARGET_PARAMETER)]
class Authenticated implements ContextualAttribute
{
    /**
     * Create a new class instance.
     */
    public function __construct(public ?string $guard = null)
    {
    }

    /**
     * Resolve the currently authenticated user.
     *
     * @param  self  $attribute
     * @param  \Kasi\Contracts\Container\Container  $container
     * @return \Kasi\Contracts\Auth\Authenticatable|null
     */
    public static function resolve(self $attribute, Container $container)
    {
        return call_user_func($container->make('auth')->userResolver(), $attribute->guard);
    }
}
