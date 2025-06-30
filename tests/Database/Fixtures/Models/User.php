<?php

namespace Kasi\Tests\Database\Fixtures\Models;

use Kasi\Foundation\Auth\User as FoundationUser;

class User extends FoundationUser
{
    protected $primaryKey = 'internal_id';
}
