<?php

namespace Kasi\Auth\Events;

use Kasi\Queue\SerializesModels;

class PasswordReset
{
    use SerializesModels;

    /**
     * The user.
     *
     * @var \Kasi\Contracts\Auth\Authenticatable
     */
    public $user;

    /**
     * Create a new event instance.
     *
     * @param  \Kasi\Contracts\Auth\Authenticatable  $user
     * @return void
     */
    public function __construct($user)
    {
        $this->user = $user;
    }
}
