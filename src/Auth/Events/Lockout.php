<?php

namespace Kasi\Auth\Events;

use Kasi\Http\Request;

class Lockout
{
    /**
     * The throttled request.
     *
     * @var \Kasi\Http\Request
     */
    public $request;

    /**
     * Create a new event instance.
     *
     * @param  \Kasi\Http\Request  $request
     * @return void
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }
}
