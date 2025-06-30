<?php

namespace Kasi\Support\Facades;

/**
 * @method static \Kasi\Http\RedirectResponse back(int $status = 302, array $headers = [], mixed $fallback = false)
 * @method static \Kasi\Http\RedirectResponse refresh(int $status = 302, array $headers = [])
 * @method static \Kasi\Http\RedirectResponse guest(string $path, int $status = 302, array $headers = [], bool|null $secure = null)
 * @method static \Kasi\Http\RedirectResponse intended(mixed $default = '/', int $status = 302, array $headers = [], bool|null $secure = null)
 * @method static \Kasi\Http\RedirectResponse to(string $path, int $status = 302, array $headers = [], bool|null $secure = null)
 * @method static \Kasi\Http\RedirectResponse away(string $path, int $status = 302, array $headers = [])
 * @method static \Kasi\Http\RedirectResponse secure(string $path, int $status = 302, array $headers = [])
 * @method static \Kasi\Http\RedirectResponse route(\BackedEnum|string $route, mixed $parameters = [], int $status = 302, array $headers = [])
 * @method static \Kasi\Http\RedirectResponse signedRoute(\BackedEnum|string $route, mixed $parameters = [], \DateTimeInterface|\DateInterval|int|null $expiration = null, int $status = 302, array $headers = [])
 * @method static \Kasi\Http\RedirectResponse temporarySignedRoute(\BackedEnum|string $route, \DateTimeInterface|\DateInterval|int|null $expiration, mixed $parameters = [], int $status = 302, array $headers = [])
 * @method static \Kasi\Http\RedirectResponse action(string|array $action, mixed $parameters = [], int $status = 302, array $headers = [])
 * @method static \Kasi\Routing\UrlGenerator getUrlGenerator()
 * @method static void setSession(\Kasi\Session\Store $session)
 * @method static string|null getIntendedUrl()
 * @method static \Kasi\Routing\Redirector setIntendedUrl(string $url)
 * @method static void macro(string $name, object|callable $macro)
 * @method static void mixin(object $mixin, bool $replace = true)
 * @method static bool hasMacro(string $name)
 * @method static void flushMacros()
 *
 * @see \Kasi\Routing\Redirector
 */
class Redirect extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'redirect';
    }
}
