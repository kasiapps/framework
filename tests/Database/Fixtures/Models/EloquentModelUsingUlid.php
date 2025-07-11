<?php

namespace Kasi\Tests\Database\Fixtures\Models;

use Kasi\Database\Eloquent\Concerns\HasUlids;
use Kasi\Database\Eloquent\Model;

class EloquentModelUsingUlid extends Model
{
    use HasUlids;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'model';

    /**
     * Get the default foreign key name for the model.
     *
     * @return string
     */
    public function getForeignKey()
    {
        return 'model_using_ulid_id';
    }
}
