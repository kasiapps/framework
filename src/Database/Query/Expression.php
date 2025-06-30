<?php

namespace Kasi\Database\Query;

use Kasi\Contracts\Database\Query\Expression as ExpressionContract;
use Kasi\Database\Grammar;

/**
 * @template TValue of string|int|float
 */
class Expression implements ExpressionContract
{
    /**
     * Create a new raw query expression.
     *
     * @param  TValue  $value
     * @return void
     */
    public function __construct(
        protected $value
    ) {
    }

    /**
     * Get the value of the expression.
     *
     * @param  \Kasi\Database\Grammar  $grammar
     * @return TValue
     */
    public function getValue(Grammar $grammar)
    {
        return $this->value;
    }
}
