<?php

namespace Kasi\Log\Context\Events;

class ContextHydrated
{
    /**
     * The context instance.
     *
     * @var \Kasi\Log\Context\Repository
     */
    public $context;

    /**
     * Create a new event instance.
     *
     * @param  \Kasi\Log\Context\Repository  $context
     */
    public function __construct($context)
    {
        $this->context = $context;
    }
}
