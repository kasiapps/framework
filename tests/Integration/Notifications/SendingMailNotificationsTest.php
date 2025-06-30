<?php

namespace Kasi\Tests\Integration\Notifications;

use Kasi\Contracts\Mail\Factory as MailFactory;
use Kasi\Contracts\Mail\Mailable;
use Kasi\Contracts\Mail\Mailer;
use Kasi\Database\Eloquent\Model;
use Kasi\Database\Schema\Blueprint;
use Kasi\Mail\Markdown;
use Kasi\Mail\Message;
use Kasi\Notifications\Channels\MailChannel;
use Kasi\Notifications\Messages\MailMessage;
use Kasi\Notifications\Notifiable;
use Kasi\Notifications\Notification;
use Kasi\Support\Facades\Schema;
use Kasi\Support\Str;
use Mockery as m;
use Orchestra\Testbench\TestCase;

class SendingMailNotificationsTest extends TestCase
{
    public $mailFactory;
    public $mailer;
    public $markdown;

    protected function defineEnvironment($app)
    {
        $this->mailFactory = m::mock(MailFactory::class);
        $this->mailer = m::mock(Mailer::class);
        $this->mailFactory->shouldReceive('mailer')->andReturn($this->mailer);
        $this->markdown = m::mock(Markdown::class);

        $app->extend(Markdown::class, function () {
            return $this->markdown;
        });

        $app->extend(Mailer::class, function () {
            return $this->mailer;
        });

        $app->extend(MailFactory::class, function () {
            return $this->mailFactory;
        });

        $app['view']->addLocation(__DIR__.'/Fixtures');
    }

    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('email');
            $table->string('name')->nullable();
        });
    }

    public function testMailIsSent()
    {
        $notification = new TestMailNotification;
        $notification->id = Str::uuid()->toString();

        $user = NotifiableUser::forceCreate([
            'email' => 'taylor@kasi.com',
        ]);

        $this->markdown->shouldReceive('theme')->twice()->with('default')->andReturn($this->markdown);
        $this->markdown->shouldReceive('render')->once()->andReturn('htmlContent');
        $this->markdown->shouldReceive('renderText')->once()->andReturn('textContent');

        $this->setMailerSendAssertions($notification, $user, function ($closure) {
            $message = m::mock(Message::class);

            $message->shouldReceive('to')->once()->with(['taylor@kasi.com']);

            $message->shouldReceive('cc')->once()->with('cc@deepblue.com', 'cc');

            $message->shouldReceive('bcc')->once()->with('bcc@deepblue.com', 'bcc');

            $message->shouldReceive('from')->once()->with('jack@deepblue.com', 'Jacques Mayol');

            $message->shouldReceive('replyTo')->once()->with('jack@deepblue.com', 'Jacques Mayol');

            $message->shouldReceive('subject')->once()->with('Test Mail Notification');

            $message->shouldReceive('priority')->once()->with(1);

            $closure($message);

            return true;
        });

        $user->notify($notification);
    }

    public function testMailIsSentWithCustomTheme()
    {
        $notification = new TestMailNotificationWithCustomTheme;
        $notification->id = Str::uuid()->toString();

        $user = NotifiableUser::forceCreate([
            'email' => 'taylor@kasi.com',
        ]);

        $this->markdown->shouldReceive('theme')->twice()->with('my-custom-theme')->andReturn($this->markdown);
        $this->markdown->shouldReceive('render')->once()->andReturn('htmlContent');
        $this->markdown->shouldReceive('renderText')->once()->andReturn('textContent');

        $this->setMailerSendAssertions($notification, $user, function ($closure) {
            $message = m::mock(Message::class);

            $message->shouldReceive('to')->once()->with(['taylor@kasi.com']);

            $message->shouldReceive('cc')->once()->with('cc@deepblue.com', 'cc');

            $message->shouldReceive('bcc')->once()->with('bcc@deepblue.com', 'bcc');

            $message->shouldReceive('from')->once()->with('jack@deepblue.com', 'Jacques Mayol');

            $message->shouldReceive('replyTo')->once()->with('jack@deepblue.com', 'Jacques Mayol');

            $message->shouldReceive('subject')->once()->with('Test Mail Notification With Custom Theme');

            $message->shouldReceive('priority')->once()->with(1);

            $closure($message);

            return true;
        });

        $user->notify($notification);
    }

    private function setMailerSendAssertions(
        Notification $notification,
        NotifiableUser $user,
        callable $callbackExpectationClosure
    ) {
        $this->mailer->shouldReceive('send')->once()->withArgs(function (...$args) use ($notification, $user, $callbackExpectationClosure) {
            $viewArray = $args[0];

            if (! m::on(fn ($closure) => $closure([]) === 'htmlContent')->match($viewArray['html'])) {
                return false;
            }

            if (! m::on(fn ($closure) => $closure([]) === 'textContent')->match($viewArray['text'])) {
                return false;
            }

            $data = $args[1];

            $expected = array_merge($notification->toMail($user)->toArray(), [
                '__kasi_notification_id' => $notification->id,
                '__kasi_notification' => get_class($notification),
                '__kasi_notification_queued' => false,
            ]);

            if (array_keys($data) !== array_keys($expected)) {
                return false;
            }
            if (array_values($data) !== array_values($expected)) {
                return false;
            }

            return m::on($callbackExpectationClosure)->match($args[2]);
        });
    }

    public function testMailIsSentToNamedAddress()
    {
        $notification = new TestMailNotification;
        $notification->id = Str::uuid()->toString();

        $user = NotifiableUserWithNamedAddress::forceCreate([
            'email' => 'taylor@kasi.com',
            'name' => 'Taylor Otwell',
        ]);

        $this->markdown->shouldReceive('theme')->twice()->with('default')->andReturn($this->markdown);
        $this->markdown->shouldReceive('render')->once()->andReturn('htmlContent');
        $this->markdown->shouldReceive('renderText')->once()->andReturn('textContent');

        $this->setMailerSendAssertions($notification, $user, function ($closure) {
            $message = m::mock(Message::class);

            $message->shouldReceive('to')->once()->with(['taylor@kasi.com' => 'Taylor Otwell', 'foo_taylor@kasi.com']);

            $message->shouldReceive('cc')->once()->with('cc@deepblue.com', 'cc');

            $message->shouldReceive('bcc')->once()->with('bcc@deepblue.com', 'bcc');

            $message->shouldReceive('from')->once()->with('jack@deepblue.com', 'Jacques Mayol');

            $message->shouldReceive('replyTo')->once()->with('jack@deepblue.com', 'Jacques Mayol');

            $message->shouldReceive('subject')->once()->with('Test Mail Notification');

            $message->shouldReceive('priority')->once()->with(1);

            $closure($message);

            return true;
        });

        $user->notify($notification);
    }

    public function testMailIsSentWithSubject()
    {
        $notification = new TestMailNotificationWithSubject;
        $notification->id = Str::uuid()->toString();

        $user = NotifiableUser::forceCreate([
            'email' => 'taylor@kasi.com',
        ]);

        $this->markdown->shouldReceive('theme')->with('default')->twice()->andReturn($this->markdown);
        $this->markdown->shouldReceive('render')->once()->andReturn('htmlContent');
        $this->markdown->shouldReceive('renderText')->once()->andReturn('textContent');

        $this->setMailerSendAssertions($notification, $user, function ($closure) {
            $message = m::mock(Message::class);

            $message->shouldReceive('to')->once()->with(['taylor@kasi.com']);

            $message->shouldReceive('subject')->once()->with('mail custom subject');

            $closure($message);

            return true;
        });

        $user->notify($notification);
    }

    public function testMailIsSentToMultipleAddresses()
    {
        $notification = new TestMailNotificationWithSubject;
        $notification->id = Str::uuid()->toString();

        $user = NotifiableUserWithMultipleAddresses::forceCreate([
            'email' => 'taylor@kasi.com',
        ]);

        $this->markdown->shouldReceive('theme')->with('default')->twice()->andReturn($this->markdown);
        $this->markdown->shouldReceive('render')->once()->andReturn('htmlContent');
        $this->markdown->shouldReceive('renderText')->once()->andReturn('textContent');

        $this->setMailerSendAssertions($notification, $user, function ($closure) {
            $message = m::mock(Message::class);

            $message->shouldReceive('to')->once()->with(['foo_taylor@kasi.com', 'bar_taylor@kasi.com']);

            $message->shouldReceive('subject')->once()->with('mail custom subject');

            $closure($message);

            return true;
        });

        $user->notify($notification);
    }

    public function testMailIsSentUsingMailable()
    {
        $notification = new TestMailNotificationWithMailable;

        $user = NotifiableUser::forceCreate([
            'email' => 'taylor@kasi.com',
        ]);

        $user->notify($notification);
    }

    public function testMailIsSentUsingMailMessageWithHtmlAndPlain()
    {
        $notification = new TestMailNotificationWithHtmlAndPlain;
        $notification->id = Str::uuid()->toString();

        $user = NotifiableUser::forceCreate([
            'email' => 'taylor@kasi.com',
        ]);

        $this->mailer->shouldReceive('send')->once()->with(
            ['html', 'plain'],
            array_merge($notification->toMail($user)->toArray(), [
                '__kasi_notification_id' => $notification->id,
                '__kasi_notification' => get_class($notification),
                '__kasi_notification_queued' => false,
            ]),
            m::on(function ($closure) {
                $message = m::mock(Message::class);

                $message->shouldReceive('to')->once()->with(['taylor@kasi.com']);

                $message->shouldReceive('subject')->once()->with('Test Mail Notification With Html And Plain');

                $closure($message);

                return true;
            })
        );

        $user->notify($notification);
    }

    public function testMailIsSentUsingMailMessageWithHtmlOnly()
    {
        $notification = new TestMailNotificationWithHtmlOnly;
        $notification->id = Str::uuid()->toString();

        $user = NotifiableUser::forceCreate([
            'email' => 'taylor@kasi.com',
        ]);

        $this->mailer->shouldReceive('send')->once()->with(
            'html',
            array_merge($notification->toMail($user)->toArray(), [
                '__kasi_notification_id' => $notification->id,
                '__kasi_notification' => get_class($notification),
                '__kasi_notification_queued' => false,
            ]),
            m::on(function ($closure) {
                $message = m::mock(Message::class);

                $message->shouldReceive('to')->once()->with(['taylor@kasi.com']);

                $message->shouldReceive('subject')->once()->with('Test Mail Notification With Html Only');

                $closure($message);

                return true;
            })
        );

        $user->notify($notification);
    }

    public function testMailIsSentUsingMailMessageWithPlainOnly()
    {
        $notification = new TestMailNotificationWithPlainOnly;
        $notification->id = Str::uuid()->toString();

        $user = NotifiableUser::forceCreate([
            'email' => 'taylor@kasi.com',
        ]);

        $this->mailer->shouldReceive('send')->once()->with(
            [null, 'plain'],
            array_merge($notification->toMail($user)->toArray(), [
                '__kasi_notification_id' => $notification->id,
                '__kasi_notification' => get_class($notification),
                '__kasi_notification_queued' => false,
            ]),
            m::on(function ($closure) {
                $message = m::mock(Message::class);

                $message->shouldReceive('to')->once()->with(['taylor@kasi.com']);

                $message->shouldReceive('subject')->once()->with('Test Mail Notification With Plain Only');

                $closure($message);

                return true;
            })
        );

        $user->notify($notification);
    }
}

class NotifiableUser extends Model
{
    use Notifiable;

    public $table = 'users';
    public $timestamps = false;
}

class NotifiableUserWithNamedAddress extends NotifiableUser
{
    public function routeNotificationForMail($notification)
    {
        return [
            $this->email => $this->name,
            'foo_'.$this->email,
        ];
    }
}

class NotifiableUserWithMultipleAddresses extends NotifiableUser
{
    public function routeNotificationForMail($notification)
    {
        return [
            'foo_'.$this->email,
            'bar_'.$this->email,
        ];
    }
}

class TestMailNotification extends Notification
{
    public function via($notifiable)
    {
        return [MailChannel::class];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->priority(1)
            ->cc('cc@deepblue.com', 'cc')
            ->bcc('bcc@deepblue.com', 'bcc')
            ->from('jack@deepblue.com', 'Jacques Mayol')
            ->replyTo('jack@deepblue.com', 'Jacques Mayol')
            ->line('The introduction to the notification.')
            ->mailer('foo');
    }
}

class TestMailNotificationWithSubject extends Notification
{
    public function via($notifiable)
    {
        return [MailChannel::class];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('mail custom subject')
            ->line('The introduction to the notification.');
    }
}

class TestMailNotificationWithMailable extends Notification
{
    public function via($notifiable)
    {
        return [MailChannel::class];
    }

    public function toMail($notifiable)
    {
        $mailable = m::mock(Mailable::class);

        $mailable->shouldReceive('send')->once();

        return $mailable;
    }
}

class TestMailNotificationWithHtmlAndPlain extends Notification
{
    public function via($notifiable)
    {
        return [MailChannel::class];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->view(['html', 'plain']);
    }
}

class TestMailNotificationWithHtmlOnly extends Notification
{
    public function via($notifiable)
    {
        return [MailChannel::class];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->view('html');
    }
}

class TestMailNotificationWithPlainOnly extends Notification
{
    public function via($notifiable)
    {
        return [MailChannel::class];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->view([null, 'plain']);
    }
}

class TestMailNotificationWithCustomTheme extends Notification
{
    public function via($notifiable)
    {
        return [MailChannel::class];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->priority(1)
            ->cc('cc@deepblue.com', 'cc')
            ->bcc('bcc@deepblue.com', 'bcc')
            ->from('jack@deepblue.com', 'Jacques Mayol')
            ->replyTo('jack@deepblue.com', 'Jacques Mayol')
            ->line('The introduction to the notification.')
            ->theme('my-custom-theme')
            ->mailer('foo');
    }
}
