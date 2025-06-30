<?php

namespace Kasi\Contracts\Support;

interface MessageProvider
{
    /**
     * Get the messages for the instance.
     *
     * @return \Kasi\Contracts\Support\MessageBag
     */
    public function getMessageBag();
}
