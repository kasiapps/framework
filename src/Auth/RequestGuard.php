<?php

namespace Kasi\Auth;

use Kasi\Contracts\Auth\Guard;
use Kasi\Contracts\Auth\UserProvider;
use Kasi\Http\Request;
use Kasi\Support\Traits\Macroable;

class RequestGuard implements Guard
{
    use GuardHelpers, Macroable;

    /**
     * The guard callback.
     *
     * @var callable
     */
    protected $callback;

    /**
     * The request instance.
     *
     * @var \Kasi\Http\Request
     */
    protected $request;

    /**
     * Create a new authentication guard.
     *
     * @param  callable  $callback
     * @param  \Kasi\Http\Request  $request
     * @param  \Kasi\Contracts\Auth\UserProvider|null  $provider
     * @return void
     */
    public function __construct(callable $callback, Request $request, ?UserProvider $provider = null)
    {
        $this->request = $request;
        $this->callback = $callback;
        $this->provider = $provider;
    }

    /**
     * Get the currently authenticated user.
     *
     * @return \Kasi\Contracts\Auth\Authenticatable|null
     */
    public function user()
    {
        // If we've already retrieved the user for the current request we can just
        // return it back immediately. We do not want to fetch the user data on
        // every call to this method because that would be tremendously slow.
        if (! is_null($this->user)) {
            return $this->user;
        }

        return $this->user = call_user_func(
            $this->callback, $this->request, $this->getProvider()
        );
    }

    /**
     * Validate a user's credentials.
     *
     * @param  array  $credentials
     * @return bool
     */
    public function validate(#[\SensitiveParameter] array $credentials = [])
    {
        return ! is_null((new static(
            $this->callback, $credentials['request'], $this->getProvider()
        ))->user());
    }

    /**
     * Set the current request instance.
     *
     * @param  \Kasi\Http\Request  $request
     * @return $this
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;

        return $this;
    }
}
