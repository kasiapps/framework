<?php

namespace Kasi\Http\Client\Events;

use Kasi\Http\Client\ConnectionException;
use Kasi\Http\Client\Request;

class ConnectionFailed
{
    /**
     * The request instance.
     *
     * @var \Kasi\Http\Client\Request
     */
    public $request;

    /**
     * The exception instance.
     *
     * @var \Kasi\Http\Client\ConnectionException
     */
    public $exception;

    /**
     * Create a new event instance.
     *
     * @param  \Kasi\Http\Client\Request  $request
     * @param  \Kasi\Http\Client\ConnectionException  $exception
     * @return void
     */
    public function __construct(Request $request, ConnectionException $exception)
    {
        $this->request = $request;
        $this->exception = $exception;
    }
}
