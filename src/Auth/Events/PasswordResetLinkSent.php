<?php

namespace Kasi\Auth\Events;

use Kasi\Queue\SerializesModels;

class PasswordResetLinkSent
{
    use SerializesModels;

    /**
     * The user instance.
     *
     * @var \Kasi\Contracts\Auth\CanResetPassword
     */
    public $user;

    /**
     * Create a new event instance.
     *
     * @param  \Kasi\Contracts\Auth\CanResetPassword  $user
     * @return void
     */
    public function __construct($user)
    {
        $this->user = $user;
    }
}
