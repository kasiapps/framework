<?php

namespace Kasi\Database\Eloquent\Casts;

use Kasi\Contracts\Database\Eloquent\Castable;
use Kasi\Contracts\Database\Eloquent\CastsAttributes;
use Kasi\Support\Stringable;

class AsStringable implements Castable
{
    /**
     * Get the caster class to use when casting from / to this cast target.
     *
     * @param  array  $arguments
     * @return \Kasi\Contracts\Database\Eloquent\CastsAttributes<\Kasi\Support\Stringable, string|\Stringable>
     */
    public static function castUsing(array $arguments)
    {
        return new class implements CastsAttributes
        {
            public function get($model, $key, $value, $attributes)
            {
                return isset($value) ? new Stringable($value) : null;
            }

            public function set($model, $key, $value, $attributes)
            {
                return isset($value) ? (string) $value : null;
            }
        };
    }
}
