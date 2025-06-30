<?php

namespace Kasi\Contracts\Auth;

interface PasswordBrokerFactory
{
    /**
     * Get a password broker instance by name.
     *
     * @param  string|null  $name
     * @return \Kasi\Contracts\Auth\PasswordBroker
     */
    public function broker($name = null);
}
