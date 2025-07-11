<?php

namespace Kasi\Cache;

class DynamoDbLock extends Lock
{
    /**
     * The DynamoDB client instance.
     *
     * @var \Kasi\Cache\DynamoDbStore
     */
    protected $dynamo;

    /**
     * Create a new lock instance.
     *
     * @param  \Kasi\Cache\DynamoDbStore  $dynamo
     * @param  string  $name
     * @param  int  $seconds
     * @param  string|null  $owner
     * @return void
     */
    public function __construct(DynamoDbStore $dynamo, $name, $seconds, $owner = null)
    {
        parent::__construct($name, $seconds, $owner);

        $this->dynamo = $dynamo;
    }

    /**
     * Attempt to acquire the lock.
     *
     * @return bool
     */
    public function acquire()
    {
        if ($this->seconds > 0) {
            return $this->dynamo->add($this->name, $this->owner, $this->seconds);
        }

        return $this->dynamo->add($this->name, $this->owner, 86400);
    }

    /**
     * Release the lock.
     *
     * @return bool
     */
    public function release()
    {
        if ($this->isOwnedByCurrentProcess()) {
            return $this->dynamo->forget($this->name);
        }

        return false;
    }

    /**
     * Release this lock in disregard of ownership.
     *
     * @return void
     */
    public function forceRelease()
    {
        $this->dynamo->forget($this->name);
    }

    /**
     * Returns the owner value written into the driver for this lock.
     *
     * @return mixed
     */
    protected function getCurrentOwner()
    {
        return $this->dynamo->get($this->name);
    }
}
