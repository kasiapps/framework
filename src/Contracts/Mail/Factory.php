<?php

namespace Kasi\Contracts\Mail;

interface Factory
{
    /**
     * Get a mailer instance by name.
     *
     * @param  string|null  $name
     * @return \Kasi\Contracts\Mail\Mailer
     */
    public function mailer($name = null);
}
