<?php

namespace Kasi\Contracts\Support;

interface DeferringDisplayableValue
{
    /**
     * Resolve the displayable value that the class is deferring.
     *
     * @return \Kasi\Contracts\Support\Htmlable|string
     */
    public function resolveDisplayableValue();
}
