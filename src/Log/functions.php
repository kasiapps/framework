<?php

namespace Kasi\Log;

if (! function_exists('Kasi\Log\log')) {
    /**
     * Log a debug message to the logs.
     *
     * @param  string|null  $message
     * @param  array  $context
     * @return ($message is null ? \Kasi\Log\LogManager : null)
     */
    function log($message = null, array $context = [])
    {
        return logger($message, $context);
    }
}
