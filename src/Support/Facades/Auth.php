<?php

namespace Kasi\Support\Facades;

use Kasi\Ui\UiServiceProvider;
use RuntimeException;

/**
 * @method static \Kasi\Contracts\Auth\Guard|\Kasi\Contracts\Auth\StatefulGuard guard(string|null $name = null)
 * @method static \Kasi\Auth\SessionGuard createSessionDriver(string $name, array $config)
 * @method static \Kasi\Auth\TokenGuard createTokenDriver(string $name, array $config)
 * @method static string getDefaultDriver()
 * @method static void shouldUse(string $name)
 * @method static void setDefaultDriver(string $name)
 * @method static \Kasi\Auth\AuthManager viaRequest(string $driver, callable $callback)
 * @method static \Closure userResolver()
 * @method static \Kasi\Auth\AuthManager resolveUsersUsing(\Closure $userResolver)
 * @method static \Kasi\Auth\AuthManager extend(string $driver, \Closure $callback)
 * @method static \Kasi\Auth\AuthManager provider(string $name, \Closure $callback)
 * @method static bool hasResolvedGuards()
 * @method static \Kasi\Auth\AuthManager forgetGuards()
 * @method static \Kasi\Auth\AuthManager setApplication(\Kasi\Contracts\Foundation\Application $app)
 * @method static \Kasi\Contracts\Auth\UserProvider|null createUserProvider(string|null $provider = null)
 * @method static string getDefaultUserProvider()
 * @method static bool check()
 * @method static bool guest()
 * @method static \Kasi\Contracts\Auth\Authenticatable|null user()
 * @method static int|string|null id()
 * @method static bool validate(array $credentials = [])
 * @method static bool hasUser()
 * @method static \Kasi\Contracts\Auth\Guard setUser(\Kasi\Contracts\Auth\Authenticatable $user)
 * @method static bool attempt(array $credentials = [], bool $remember = false)
 * @method static bool once(array $credentials = [])
 * @method static void login(\Kasi\Contracts\Auth\Authenticatable $user, bool $remember = false)
 * @method static \Kasi\Contracts\Auth\Authenticatable|false loginUsingId(mixed $id, bool $remember = false)
 * @method static \Kasi\Contracts\Auth\Authenticatable|false onceUsingId(mixed $id)
 * @method static bool viaRemember()
 * @method static void logout()
 * @method static \Symfony\Component\HttpFoundation\Response|null basic(string $field = 'email', array $extraConditions = [])
 * @method static \Symfony\Component\HttpFoundation\Response|null onceBasic(string $field = 'email', array $extraConditions = [])
 * @method static bool attemptWhen(array $credentials = [], array|callable|null $callbacks = null, bool $remember = false)
 * @method static void logoutCurrentDevice()
 * @method static \Kasi\Contracts\Auth\Authenticatable|null logoutOtherDevices(string $password)
 * @method static void attempting(mixed $callback)
 * @method static \Kasi\Contracts\Auth\Authenticatable getLastAttempted()
 * @method static string getName()
 * @method static string getRecallerName()
 * @method static \Kasi\Auth\SessionGuard setRememberDuration(int $minutes)
 * @method static \Kasi\Contracts\Cookie\QueueingFactory getCookieJar()
 * @method static void setCookieJar(\Kasi\Contracts\Cookie\QueueingFactory $cookie)
 * @method static \Kasi\Contracts\Events\Dispatcher getDispatcher()
 * @method static void setDispatcher(\Kasi\Contracts\Events\Dispatcher $events)
 * @method static \Kasi\Contracts\Session\Session getSession()
 * @method static \Kasi\Contracts\Auth\Authenticatable|null getUser()
 * @method static \Symfony\Component\HttpFoundation\Request getRequest()
 * @method static \Kasi\Auth\SessionGuard setRequest(\Symfony\Component\HttpFoundation\Request $request)
 * @method static \Kasi\Support\Timebox getTimebox()
 * @method static \Kasi\Contracts\Auth\Authenticatable authenticate()
 * @method static \Kasi\Auth\SessionGuard forgetUser()
 * @method static \Kasi\Contracts\Auth\UserProvider getProvider()
 * @method static void setProvider(\Kasi\Contracts\Auth\UserProvider $provider)
 * @method static void macro(string $name, object|callable $macro)
 * @method static void mixin(object $mixin, bool $replace = true)
 * @method static bool hasMacro(string $name)
 * @method static void flushMacros()
 *
 * @see \Kasi\Auth\AuthManager
 * @see \Kasi\Auth\SessionGuard
 */
class Auth extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'auth';
    }

    /**
     * Register the typical authentication routes for an application.
     *
     * @param  array  $options
     * @return void
     *
     * @throws \RuntimeException
     */
    public static function routes(array $options = [])
    {
        if (! static::$app->providerIsLoaded(UiServiceProvider::class)) {
            throw new RuntimeException('In order to use the Auth::routes() method, please install the kasi/ui package.');
        }

        static::$app->make('router')->auth($options);
    }
}
