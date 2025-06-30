<?php

namespace Kasi\Queue\Connectors;

use Kasi\Queue\SyncQueue;

class SyncConnector implements ConnectorInterface
{
    /**
     * Establish a queue connection.
     *
     * @param  array  $config
     * @return \Kasi\Contracts\Queue\Queue
     */
    public function connect(array $config)
    {
        return new SyncQueue($config['after_commit'] ?? null);
    }
}
