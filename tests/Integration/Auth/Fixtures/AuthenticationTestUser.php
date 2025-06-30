<?php

namespace Kasi\Tests\Integration\Auth\Fixtures;

use Kasi\Foundation\Auth\User as Authenticatable;
use Kasi\Notifications\Notifiable;

class AuthenticationTestUser extends Authenticatable
{
    use Notifiable;

    public $table = 'users';
    public $timestamps = false;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var string[]
     */
    protected $guarded = [];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var string[]
     */
    protected $hidden = [
        'password', 'remember_token',
    ];
}
