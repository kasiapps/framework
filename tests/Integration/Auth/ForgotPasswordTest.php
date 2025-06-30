<?php

namespace Kasi\Tests\Integration\Auth;

use Kasi\Auth\Events\PasswordResetLinkSent;
use Kasi\Auth\Notifications\ResetPassword;
use Kasi\Foundation\Testing\RefreshDatabase;
use Kasi\Notifications\Messages\MailMessage;
use Kasi\Support\Facades\Event;
use Kasi\Support\Facades\Notification;
use Kasi\Support\Facades\Password;
use Kasi\Support\Str;
use Kasi\Tests\Integration\Auth\Fixtures\AuthenticationTestUser;
use Orchestra\Testbench\Attributes\WithMigration;
use Orchestra\Testbench\Factories\UserFactory;
use Orchestra\Testbench\TestCase;

#[WithMigration]
class ForgotPasswordTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        ResetPassword::$createUrlCallback = null;
        ResetPassword::$toMailCallback = null;

        parent::tearDown();
    }

    protected function defineEnvironment($app)
    {
        $app['config']->set('app.key', Str::random(32));
        $app['config']->set('auth.providers.users.model', AuthenticationTestUser::class);
    }

    protected function defineRoutes($router)
    {
        $router->get('password/reset/{token}', function ($token) {
            return 'Reset password!';
        })->name('password.reset');

        $router->get('custom/password/reset/{token}', function ($token) {
            return 'Custom reset password!';
        })->name('custom.password.reset');
    }

    public function testItCanSendForgotPasswordEmail()
    {
        Notification::fake();

        UserFactory::new()->create();

        $user = AuthenticationTestUser::first();

        Password::broker()->sendResetLink([
            'email' => $user->email,
        ]);

        Notification::assertSentTo(
            $user,
            function (ResetPassword $notification, $channels) use ($user) {
                $message = $notification->toMail($user);

                return ! is_null($notification->token)
                    && $message->actionUrl === route('password.reset', ['token' => $notification->token, 'email' => $user->email]);
            }
        );
    }

    public function testItCanTriggerPasswordResetSentEvent()
    {
        Event::fake([PasswordResetLinkSent::class]);

        UserFactory::new()->create();

        $user = AuthenticationTestUser::first();

        Password::broker()->sendResetLink([
            'email' => $user->email,
        ]);

        Event::assertDispatched(PasswordResetLinkSent::class, function ($event) {
            $this->assertEquals(1, $event->user->id);

            return true;
        });
    }

    public function testItCanSendForgotPasswordEmailViaCreateUrlUsing()
    {
        Notification::fake();

        ResetPassword::createUrlUsing(function ($user, string $token) {
            return route('custom.password.reset', $token);
        });

        UserFactory::new()->create();

        $user = AuthenticationTestUser::first();

        Password::broker()->sendResetLink([
            'email' => $user->email,
        ]);

        Notification::assertSentTo(
            $user,
            function (ResetPassword $notification, $channels) use ($user) {
                $message = $notification->toMail($user);

                return ! is_null($notification->token)
                    && $message->actionUrl === route('custom.password.reset', ['token' => $notification->token]);
            }
        );
    }

    public function testItCanSendForgotPasswordEmailViaToMailUsing()
    {
        Notification::fake();

        ResetPassword::toMailUsing(function ($notifiable, $token) {
            return (new MailMessage)
                ->subject(__('Reset Password Notification'))
                ->line(__('You are receiving this email because we received a password reset request for your account.'))
                ->action(__('Reset Password'), route('custom.password.reset', $token))
                ->line(__('If you did not request a password reset, no further action is required.'));
        });

        UserFactory::new()->create();

        $user = AuthenticationTestUser::first();

        Password::broker()->sendResetLink([
            'email' => $user->email,
        ]);

        Notification::assertSentTo(
            $user,
            function (ResetPassword $notification, $channels) use ($user) {
                $message = $notification->toMail($user);

                return ! is_null($notification->token)
                    && $message->actionUrl === route('custom.password.reset', ['token' => $notification->token]);
            }
        );
    }
}
