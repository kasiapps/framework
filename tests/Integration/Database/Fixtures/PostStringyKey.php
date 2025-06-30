<?php

namespace Kasi\Tests\Integration\Database\Fixtures;

use Kasi\Database\Eloquent\Model;

class PostStringyKey extends Model
{
    public $table = 'my_posts';

    public $primaryKey = 'my_id';
}
