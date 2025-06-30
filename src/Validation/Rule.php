<?php

namespace Kasi\Validation;

use Kasi\Contracts\Support\Arrayable;
use Kasi\Support\Traits\Macroable;
use Kasi\Validation\Rules\ArrayRule;
use Kasi\Validation\Rules\Can;
use Kasi\Validation\Rules\Date;
use Kasi\Validation\Rules\Dimensions;
use Kasi\Validation\Rules\Email;
use Kasi\Validation\Rules\Enum;
use Kasi\Validation\Rules\ExcludeIf;
use Kasi\Validation\Rules\Exists;
use Kasi\Validation\Rules\File;
use Kasi\Validation\Rules\ImageFile;
use Kasi\Validation\Rules\In;
use Kasi\Validation\Rules\NotIn;
use Kasi\Validation\Rules\Numeric;
use Kasi\Validation\Rules\ProhibitedIf;
use Kasi\Validation\Rules\RequiredIf;
use Kasi\Validation\Rules\Unique;

class Rule
{
    use Macroable;

    /**
     * Get a can constraint builder instance.
     *
     * @param  string  $ability
     * @param  mixed  ...$arguments
     * @return \Kasi\Validation\Rules\Can
     */
    public static function can($ability, ...$arguments)
    {
        return new Can($ability, $arguments);
    }

    /**
     * Apply the given rules if the given condition is truthy.
     *
     * @param  callable|bool  $condition
     * @param  \Kasi\Contracts\Validation\ValidationRule|\Kasi\Contracts\Validation\InvokableRule|\Kasi\Contracts\Validation\Rule|\Closure|array|string  $rules
     * @param  \Kasi\Contracts\Validation\ValidationRule|\Kasi\Contracts\Validation\InvokableRule|\Kasi\Contracts\Validation\Rule|\Closure|array|string  $defaultRules
     * @return \Kasi\Validation\ConditionalRules
     */
    public static function when($condition, $rules, $defaultRules = [])
    {
        return new ConditionalRules($condition, $rules, $defaultRules);
    }

    /**
     * Apply the given rules if the given condition is falsy.
     *
     * @param  callable|bool  $condition
     * @param  \Kasi\Contracts\Validation\ValidationRule|\Kasi\Contracts\Validation\InvokableRule|\Kasi\Contracts\Validation\Rule|\Closure|array|string  $rules
     * @param  \Kasi\Contracts\Validation\ValidationRule|\Kasi\Contracts\Validation\InvokableRule|\Kasi\Contracts\Validation\Rule|\Closure|array|string  $defaultRules
     * @return \Kasi\Validation\ConditionalRules
     */
    public static function unless($condition, $rules, $defaultRules = [])
    {
        return new ConditionalRules($condition, $defaultRules, $rules);
    }

    /**
     * Get an array rule builder instance.
     *
     * @param  array|null  $keys
     * @return \Kasi\Validation\Rules\ArrayRule
     */
    public static function array($keys = null)
    {
        return new ArrayRule(...func_get_args());
    }

    /**
     * Create a new nested rule set.
     *
     * @param  callable  $callback
     * @return \Kasi\Validation\NestedRules
     */
    public static function forEach($callback)
    {
        return new NestedRules($callback);
    }

    /**
     * Get a unique constraint builder instance.
     *
     * @param  string  $table
     * @param  string  $column
     * @return \Kasi\Validation\Rules\Unique
     */
    public static function unique($table, $column = 'NULL')
    {
        return new Unique($table, $column);
    }

    /**
     * Get an exists constraint builder instance.
     *
     * @param  string  $table
     * @param  string  $column
     * @return \Kasi\Validation\Rules\Exists
     */
    public static function exists($table, $column = 'NULL')
    {
        return new Exists($table, $column);
    }

    /**
     * Get an in rule builder instance.
     *
     * @param  \Kasi\Contracts\Support\Arrayable|\BackedEnum|\UnitEnum|array|string  $values
     * @return \Kasi\Validation\Rules\In
     */
    public static function in($values)
    {
        if ($values instanceof Arrayable) {
            $values = $values->toArray();
        }

        return new In(is_array($values) ? $values : func_get_args());
    }

    /**
     * Get a not_in rule builder instance.
     *
     * @param  \Kasi\Contracts\Support\Arrayable|\BackedEnum|\UnitEnum|array|string  $values
     * @return \Kasi\Validation\Rules\NotIn
     */
    public static function notIn($values)
    {
        if ($values instanceof Arrayable) {
            $values = $values->toArray();
        }

        return new NotIn(is_array($values) ? $values : func_get_args());
    }

    /**
     * Get a required_if rule builder instance.
     *
     * @param  callable|bool  $callback
     * @return \Kasi\Validation\Rules\RequiredIf
     */
    public static function requiredIf($callback)
    {
        return new RequiredIf($callback);
    }

    /**
     * Get a exclude_if rule builder instance.
     *
     * @param  callable|bool  $callback
     * @return \Kasi\Validation\Rules\ExcludeIf
     */
    public static function excludeIf($callback)
    {
        return new ExcludeIf($callback);
    }

    /**
     * Get a prohibited_if rule builder instance.
     *
     * @param  callable|bool  $callback
     * @return \Kasi\Validation\Rules\ProhibitedIf
     */
    public static function prohibitedIf($callback)
    {
        return new ProhibitedIf($callback);
    }

    /**
     * Get a date rule builder instance.
     *
     * @return \Kasi\Validation\Rules\Date
     */
    public static function date()
    {
        return new Date;
    }

    /**
     * Get an email rule builder instance.
     *
     * @return \Kasi\Validation\Rules\Email
     */
    public static function email()
    {
        return new Email;
    }

    /**
     * Get an enum rule builder instance.
     *
     * @param  class-string  $type
     * @return \Kasi\Validation\Rules\Enum
     */
    public static function enum($type)
    {
        return new Enum($type);
    }

    /**
     * Get a file rule builder instance.
     *
     * @return \Kasi\Validation\Rules\File
     */
    public static function file()
    {
        return new File;
    }

    /**
     * Get an image file rule builder instance.
     *
     * @return \Kasi\Validation\Rules\ImageFile
     */
    public static function imageFile()
    {
        return new ImageFile;
    }

    /**
     * Get a dimensions rule builder instance.
     *
     * @param  array  $constraints
     * @return \Kasi\Validation\Rules\Dimensions
     */
    public static function dimensions(array $constraints = [])
    {
        return new Dimensions($constraints);
    }

    /**
     * Get a numeric rule builder instance.
     *
     * @return \Kasi\Validation\Rules\Numeric
     */
    public static function numeric()
    {
        return new Numeric;
    }
}
