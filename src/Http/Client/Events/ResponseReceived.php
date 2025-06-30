<?php

namespace Kasi\Http\Client\Events;

use Kasi\Http\Client\Request;
use Kasi\Http\Client\Response;

class ResponseReceived
{
    /**
     * The request instance.
     *
     * @var \Kasi\Http\Client\Request
     */
    public $request;

    /**
     * The response instance.
     *
     * @var \Kasi\Http\Client\Response
     */
    public $response;

    /**
     * Create a new event instance.
     *
     * @param  \Kasi\Http\Client\Request  $request
     * @param  \Kasi\Http\Client\Response  $response
     * @return void
     */
    public function __construct(Request $request, Response $response)
    {
        $this->request = $request;
        $this->response = $response;
    }
}
