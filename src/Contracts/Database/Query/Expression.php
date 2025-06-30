<?php

namespace Kasi\Contracts\Database\Query;

use Kasi\Database\Grammar;

interface Expression
{
    /**
     * Get the value of the expression.
     *
     * @param  \Kasi\Database\Grammar  $grammar
     * @return string|int|float
     */
    public function getValue(Grammar $grammar);
}
