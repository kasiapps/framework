<?php

namespace Kasi\Auth\Events;

use Kasi\Queue\SerializesModels;

class Verified
{
    use SerializesModels;

    /**
     * The verified user.
     *
     * @var \Kasi\Contracts\Auth\MustVerifyEmail
     */
    public $user;

    /**
     * Create a new event instance.
     *
     * @param  \Kasi\Contracts\Auth\MustVerifyEmail  $user
     * @return void
     */
    public function __construct($user)
    {
        $this->user = $user;
    }
}
