<?php

namespace Kasi\Database\Eloquent\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class UseFactory
{
    /**
     * Create a new attribute instance.
     *
     * @param  class-string<\Kasi\Database\Eloquent\Factories\Factory>  $factoryClass
     * @return void
     */
    public function __construct(public string $factoryClass)
    {
    }
}
