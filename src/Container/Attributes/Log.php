<?php

namespace Kasi\Container\Attributes;

use Attribute;
use Kasi\Contracts\Container\Container;
use Kasi\Contracts\Container\ContextualAttribute;

#[Attribute(Attribute::TARGET_PARAMETER)]
class Log implements ContextualAttribute
{
    /**
     * Create a new class instance.
     */
    public function __construct(public ?string $channel = null)
    {
    }

    /**
     * Resolve the log channel.
     *
     * @param  self  $attribute
     * @param  \Kasi\Contracts\Container\Container  $container
     * @return \Psr\Log\LoggerInterface
     */
    public static function resolve(self $attribute, Container $container)
    {
        return $container->make('log')->channel($attribute->channel);
    }
}
