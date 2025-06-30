<?php

namespace Kasi\Tests\Integration\Http\Fixtures;

use Kasi\Database\Eloquent\Model;

class Author extends Model
{
    /**
     * The attributes that aren't mass assignable.
     *
     * @var string[]
     */
    protected $guarded = [];
}
