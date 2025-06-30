<?php

namespace Kasi\Queue\Connectors;

interface ConnectorInterface
{
    /**
     * Establish a queue connection.
     *
     * @param  array  $config
     * @return \Kasi\Contracts\Queue\Queue
     */
    public function connect(array $config);
}
