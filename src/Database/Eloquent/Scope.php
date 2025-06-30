<?php

namespace Kasi\Database\Eloquent;

interface Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param  \Kasi\Database\Eloquent\Builder  $builder
     * @param  \Kasi\Database\Eloquent\Model  $model
     * @return void
     */
    public function apply(Builder $builder, Model $model);
}
