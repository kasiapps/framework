<?php

namespace Kasi\Http\Client\Events;

use Kasi\Http\Client\Request;

class RequestSending
{
    /**
     * The request instance.
     *
     * @var \Kasi\Http\Client\Request
     */
    public $request;

    /**
     * Create a new event instance.
     *
     * @param  \Kasi\Http\Client\Request  $request
     * @return void
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }
}
