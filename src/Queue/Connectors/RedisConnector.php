<?php

namespace Kasi\Queue\Connectors;

use Kasi\Contracts\Redis\Factory as Redis;
use Kasi\Queue\RedisQueue;

class RedisConnector implements ConnectorInterface
{
    /**
     * The Redis database instance.
     *
     * @var \Kasi\Contracts\Redis\Factory
     */
    protected $redis;

    /**
     * The connection name.
     *
     * @var string
     */
    protected $connection;

    /**
     * Create a new Redis queue connector instance.
     *
     * @param  \Kasi\Contracts\Redis\Factory  $redis
     * @param  string|null  $connection
     * @return void
     */
    public function __construct(Redis $redis, $connection = null)
    {
        $this->redis = $redis;
        $this->connection = $connection;
    }

    /**
     * Establish a queue connection.
     *
     * @param  array  $config
     * @return \Kasi\Contracts\Queue\Queue
     */
    public function connect(array $config)
    {
        return new RedisQueue(
            $this->redis, $config['queue'],
            $config['connection'] ?? $this->connection,
            $config['retry_after'] ?? 60,
            $config['block_for'] ?? null,
            $config['after_commit'] ?? null,
            $config['migration_batch_size'] ?? -1
        );
    }
}
