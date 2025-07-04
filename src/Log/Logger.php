<?php

namespace Kasi\Log;

use Closure;
use Kasi\Contracts\Events\Dispatcher;
use Kasi\Contracts\Support\Arrayable;
use Kasi\Contracts\Support\Jsonable;
use Kasi\Log\Events\MessageLogged;
use Kasi\Support\Traits\Conditionable;
use Psr\Log\LoggerInterface;
use RuntimeException;

class Logger implements LoggerInterface
{
    use Conditionable;

    /**
     * The underlying logger implementation.
     *
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * The event dispatcher instance.
     *
     * @var \Kasi\Contracts\Events\Dispatcher|null
     */
    protected $dispatcher;

    /**
     * Any context to be added to logs.
     *
     * @var array
     */
    protected $context = [];

    /**
     * Create a new log writer instance.
     *
     * @param  \Psr\Log\LoggerInterface  $logger
     * @param  \Kasi\Contracts\Events\Dispatcher|null  $dispatcher
     * @return void
     */
    public function __construct(LoggerInterface $logger, ?Dispatcher $dispatcher = null)
    {
        $this->logger = $logger;
        $this->dispatcher = $dispatcher;
    }

    /**
     * Log an emergency message to the logs.
     *
     * @param  \Kasi\Contracts\Support\Arrayable|\Kasi\Contracts\Support\Jsonable|\Kasi\Support\Stringable|array|string  $message
     * @param  array  $context
     * @return void
     */
    public function emergency($message, array $context = []): void
    {
        $this->writeLog(__FUNCTION__, $message, $context);
    }

    /**
     * Log an alert message to the logs.
     *
     * @param  \Kasi\Contracts\Support\Arrayable|\Kasi\Contracts\Support\Jsonable|\Kasi\Support\Stringable|array|string  $message
     * @param  array  $context
     * @return void
     */
    public function alert($message, array $context = []): void
    {
        $this->writeLog(__FUNCTION__, $message, $context);
    }

    /**
     * Log a critical message to the logs.
     *
     * @param  \Kasi\Contracts\Support\Arrayable|\Kasi\Contracts\Support\Jsonable|\Kasi\Support\Stringable|array|string  $message
     * @param  array  $context
     * @return void
     */
    public function critical($message, array $context = []): void
    {
        $this->writeLog(__FUNCTION__, $message, $context);
    }

    /**
     * Log an error message to the logs.
     *
     * @param  \Kasi\Contracts\Support\Arrayable|\Kasi\Contracts\Support\Jsonable|\Kasi\Support\Stringable|array|string  $message
     * @param  array  $context
     * @return void
     */
    public function error($message, array $context = []): void
    {
        $this->writeLog(__FUNCTION__, $message, $context);
    }

    /**
     * Log a warning message to the logs.
     *
     * @param  \Kasi\Contracts\Support\Arrayable|\Kasi\Contracts\Support\Jsonable|\Kasi\Support\Stringable|array|string  $message
     * @param  array  $context
     * @return void
     */
    public function warning($message, array $context = []): void
    {
        $this->writeLog(__FUNCTION__, $message, $context);
    }

    /**
     * Log a notice to the logs.
     *
     * @param  \Kasi\Contracts\Support\Arrayable|\Kasi\Contracts\Support\Jsonable|\Kasi\Support\Stringable|array|string  $message
     * @param  array  $context
     * @return void
     */
    public function notice($message, array $context = []): void
    {
        $this->writeLog(__FUNCTION__, $message, $context);
    }

    /**
     * Log an informational message to the logs.
     *
     * @param  \Kasi\Contracts\Support\Arrayable|\Kasi\Contracts\Support\Jsonable|\Kasi\Support\Stringable|array|string  $message
     * @param  array  $context
     * @return void
     */
    public function info($message, array $context = []): void
    {
        $this->writeLog(__FUNCTION__, $message, $context);
    }

    /**
     * Log a debug message to the logs.
     *
     * @param  \Kasi\Contracts\Support\Arrayable|\Kasi\Contracts\Support\Jsonable|\Kasi\Support\Stringable|array|string  $message
     * @param  array  $context
     * @return void
     */
    public function debug($message, array $context = []): void
    {
        $this->writeLog(__FUNCTION__, $message, $context);
    }

    /**
     * Log a message to the logs.
     *
     * @param  string  $level
     * @param  \Kasi\Contracts\Support\Arrayable|\Kasi\Contracts\Support\Jsonable|\Kasi\Support\Stringable|array|string  $message
     * @param  array  $context
     * @return void
     */
    public function log($level, $message, array $context = []): void
    {
        $this->writeLog($level, $message, $context);
    }

    /**
     * Dynamically pass log calls into the writer.
     *
     * @param  string  $level
     * @param  \Kasi\Contracts\Support\Arrayable|\Kasi\Contracts\Support\Jsonable|\Kasi\Support\Stringable|array|string  $message
     * @param  array  $context
     * @return void
     */
    public function write($level, $message, array $context = []): void
    {
        $this->writeLog($level, $message, $context);
    }

    /**
     * Write a message to the log.
     *
     * @param  string  $level
     * @param  \Kasi\Contracts\Support\Arrayable|\Kasi\Contracts\Support\Jsonable|\Kasi\Support\Stringable|array|string  $message
     * @param  array  $context
     * @return void
     */
    protected function writeLog($level, $message, $context): void
    {
        $this->logger->{$level}(
            $message = $this->formatMessage($message),
            $context = array_merge($this->context, $context)
        );

        $this->fireLogEvent($level, $message, $context);
    }

    /**
     * Add context to all future logs.
     *
     * @param  array  $context
     * @return $this
     */
    public function withContext(array $context = [])
    {
        $this->context = array_merge($this->context, $context);

        return $this;
    }

    /**
     * Flush the existing context array.
     *
     * @return $this
     */
    public function withoutContext()
    {
        $this->context = [];

        return $this;
    }

    /**
     * Register a new callback handler for when a log event is triggered.
     *
     * @param  \Closure  $callback
     * @return void
     *
     * @throws \RuntimeException
     */
    public function listen(Closure $callback)
    {
        if (! isset($this->dispatcher)) {
            throw new RuntimeException('Events dispatcher has not been set.');
        }

        $this->dispatcher->listen(MessageLogged::class, $callback);
    }

    /**
     * Fires a log event.
     *
     * @param  string  $level
     * @param  string  $message
     * @param  array  $context
     * @return void
     */
    protected function fireLogEvent($level, $message, array $context = [])
    {
        // If the event dispatcher is set, we will pass along the parameters to the
        // log listeners. These are useful for building profilers or other tools
        // that aggregate all of the log messages for a given "request" cycle.
        $this->dispatcher?->dispatch(new MessageLogged($level, $message, $context));
    }

    /**
     * Format the parameters for the logger.
     *
     * @param  \Kasi\Contracts\Support\Arrayable|\Kasi\Contracts\Support\Jsonable|\Kasi\Support\Stringable|array|string  $message
     * @return string
     */
    protected function formatMessage($message)
    {
        if (is_array($message)) {
            return var_export($message, true);
        } elseif ($message instanceof Jsonable) {
            return $message->toJson();
        } elseif ($message instanceof Arrayable) {
            return var_export($message->toArray(), true);
        }

        return (string) $message;
    }

    /**
     * Get the underlying logger implementation.
     *
     * @return \Psr\Log\LoggerInterface
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * Get the event dispatcher instance.
     *
     * @return \Kasi\Contracts\Events\Dispatcher
     */
    public function getEventDispatcher()
    {
        return $this->dispatcher;
    }

    /**
     * Set the event dispatcher instance.
     *
     * @param  \Kasi\Contracts\Events\Dispatcher  $dispatcher
     * @return void
     */
    public function setEventDispatcher(Dispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    /**
     * Dynamically proxy method calls to the underlying logger.
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return $this->logger->{$method}(...$parameters);
    }
}
