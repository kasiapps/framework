<?php

declare(strict_types=1);

namespace Kasi\Container\Attributes;

use Attribute;
use Kasi\Contracts\Container\Container;
use Kasi\Contracts\Container\ContextualAttribute;

#[Attribute(Attribute::TARGET_PARAMETER)]
final class Tag implements ContextualAttribute
{
    public function __construct(
        public string $tag,
    ) {
    }

    /**
     * Resolve the tag.
     *
     * @param  self  $attribute
     * @param  \Kasi\Contracts\Container\Container  $container
     * @return mixed
     */
    public static function resolve(self $attribute, Container $container)
    {
        return $container->tagged($attribute->tag);
    }
}
