<?php

namespace Kasi\Validation\Rules;

use Kasi\Contracts\Support\Arrayable;
use Stringable;

use function Kasi\Support\enum_value;

class NotIn implements Stringable
{
    /**
     * The name of the rule.
     *
     * @var string
     */
    protected $rule = 'not_in';

    /**
     * The accepted values.
     *
     * @var array
     */
    protected $values;

    /**
     * Create a new "not in" rule instance.
     *
     * @param  \Kasi\Contracts\Support\Arrayable|\BackedEnum|\UnitEnum|array|string  $values
     * @return void
     */
    public function __construct($values)
    {
        if ($values instanceof Arrayable) {
            $values = $values->toArray();
        }

        $this->values = is_array($values) ? $values : func_get_args();
    }

    /**
     * Convert the rule to a validation string.
     *
     * @return string
     */
    public function __toString()
    {
        $values = array_map(function ($value) {
            $value = enum_value($value);

            return '"'.str_replace('"', '""', $value).'"';
        }, $this->values);

        return $this->rule.':'.implode(',', $values);
    }
}
