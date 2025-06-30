<?php

namespace Kasi\Queue\Middleware;

use Kasi\Container\Container;
use Kasi\Contracts\Redis\Factory as Redis;
use Kasi\Redis\Limiters\DurationLimiter;
use Kasi\Support\InteractsWithTime;
use Throwable;

class ThrottlesExceptionsWithRedis extends ThrottlesExceptions
{
    use InteractsWithTime;

    /**
     * The Redis factory implementation.
     *
     * @var \Kasi\Contracts\Redis\Factory
     */
    protected $redis;

    /**
     * The rate limiter instance.
     *
     * @var \Kasi\Redis\Limiters\DurationLimiter
     */
    protected $limiter;

    /**
     * Process the job.
     *
     * @param  mixed  $job
     * @param  callable  $next
     * @return mixed
     */
    public function handle($job, $next)
    {
        $this->redis = Container::getInstance()->make(Redis::class);

        $this->limiter = new DurationLimiter(
            $this->redis, $this->getKey($job), $this->maxAttempts, $this->decaySeconds
        );

        if ($this->limiter->tooManyAttempts()) {
            return $job->release($this->limiter->decaysAt - $this->currentTime());
        }

        try {
            $next($job);

            $this->limiter->clear();
        } catch (Throwable $throwable) {
            if ($this->whenCallback && ! call_user_func($this->whenCallback, $throwable)) {
                throw $throwable;
            }

            if ($this->reportCallback && call_user_func($this->reportCallback, $throwable)) {
                report($throwable);
            }

            $this->limiter->acquire();

            return $job->release($this->retryAfterMinutes * 60);
        }
    }
}
