<?php

namespace Kasi\Tests\Notifications;

use Kasi\Container\Container;
use Kasi\Contracts\Notifications\Dispatcher;
use Kasi\Notifications\RoutesNotifications;
use Kasi\Support\Facades\Notification;
use InvalidArgumentException;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use stdClass;

class NotificationRoutesNotificationsTest extends TestCase
{
    protected function tearDown(): void
    {
        m::close();

        Container::setInstance(null);
    }

    public function testNotificationCanBeDispatched()
    {
        $container = new Container;
        $factory = m::mock(Dispatcher::class);
        $container->instance(Dispatcher::class, $factory);
        $notifiable = new RoutesNotificationsTestInstance;
        $instance = new stdClass;
        $factory->shouldReceive('send')->with($notifiable, $instance);
        Container::setInstance($container);

        $notifiable->notify($instance);
    }

    public function testNotificationCanBeSentNow()
    {
        $container = new Container;
        $factory = m::mock(Dispatcher::class);
        $container->instance(Dispatcher::class, $factory);
        $notifiable = new RoutesNotificationsTestInstance;
        $instance = new stdClass;
        $factory->shouldReceive('sendNow')->with($notifiable, $instance, null);
        Container::setInstance($container);

        $notifiable->notifyNow($instance);
    }

    public function testNotificationOptionRouting()
    {
        $instance = new RoutesNotificationsTestInstance;
        $this->assertSame('bar', $instance->routeNotificationFor('foo'));
        $this->assertSame('taylor@kasi.com', $instance->routeNotificationFor('mail'));
    }

    public function testOnDemandNotificationsCannotUseDatabaseChannel()
    {
        $this->expectExceptionObject(
            new InvalidArgumentException('The database channel does not support on-demand notifications.')
        );

        Notification::route('database', 'foo');
    }
}

class RoutesNotificationsTestInstance
{
    use RoutesNotifications;

    protected $email = 'taylor@kasi.com';

    public function routeNotificationForFoo()
    {
        return 'bar';
    }
}
