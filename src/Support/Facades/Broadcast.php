<?php

namespace Kasi\Support\Facades;

use Kasi\Contracts\Broadcasting\Factory as BroadcastingFactoryContract;

/**
 * @method static void routes(array|null $attributes = null)
 * @method static void userRoutes(array|null $attributes = null)
 * @method static void channelRoutes(array|null $attributes = null)
 * @method static string|null socket(\Kasi\Http\Request|null $request = null)
 * @method static \Kasi\Broadcasting\AnonymousEvent on(\Kasi\Broadcasting\Channel|array|string $channels)
 * @method static \Kasi\Broadcasting\AnonymousEvent private(string $channel)
 * @method static \Kasi\Broadcasting\AnonymousEvent presence(string $channel)
 * @method static \Kasi\Broadcasting\PendingBroadcast event(mixed|null $event = null)
 * @method static void queue(mixed $event)
 * @method static mixed connection(string|null $driver = null)
 * @method static mixed driver(string|null $name = null)
 * @method static \Pusher\Pusher pusher(array $config)
 * @method static \Ably\AblyRest ably(array $config)
 * @method static string getDefaultDriver()
 * @method static void setDefaultDriver(string $name)
 * @method static void purge(string|null $name = null)
 * @method static \Kasi\Broadcasting\BroadcastManager extend(string $driver, \Closure $callback)
 * @method static \Kasi\Contracts\Foundation\Application getApplication()
 * @method static \Kasi\Broadcasting\BroadcastManager setApplication(\Kasi\Contracts\Foundation\Application $app)
 * @method static \Kasi\Broadcasting\BroadcastManager forgetDrivers()
 * @method static mixed auth(\Kasi\Http\Request $request)
 * @method static mixed validAuthenticationResponse(\Kasi\Http\Request $request, mixed $result)
 * @method static void broadcast(array $channels, string $event, array $payload = [])
 * @method static array|null resolveAuthenticatedUser(\Kasi\Http\Request $request)
 * @method static void resolveAuthenticatedUserUsing(\Closure $callback)
 * @method static \Kasi\Broadcasting\Broadcasters\Broadcaster channel(\Kasi\Contracts\Broadcasting\HasBroadcastChannel|string $channel, callable|string $callback, array $options = [])
 * @method static \Kasi\Support\Collection getChannels()
 *
 * @see \Kasi\Broadcasting\BroadcastManager
 * @see \Kasi\Broadcasting\Broadcasters\Broadcaster
 */
class Broadcast extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return BroadcastingFactoryContract::class;
    }
}
