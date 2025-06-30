<?php

namespace Kasi\Tests\Auth;

use Kasi\Auth\Events\Registered;
use Kasi\Auth\Listeners\SendEmailVerificationNotification;
use Kasi\Contracts\Auth\MustVerifyEmail;
use Kasi\Foundation\Auth\User;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class AuthListenersSendEmailVerificationNotificationHandleFunctionTest extends TestCase
{
    /**
     * @return void
     */
    public function testWillExecuted()
    {
        $user = $this->getMockBuilder(MustVerifyEmail::class)->getMock();
        $user->method('hasVerifiedEmail')->willReturn(false);
        $user->expects($this->once())->method('sendEmailVerificationNotification');

        $listener = new SendEmailVerificationNotification;

        $listener->handle(new Registered($user));
    }

    /**
     * @return void
     */
    public function testUserIsNotInstanceOfMustVerifyEmail()
    {
        $user = m::mock(User::class);
        $user->shouldNotReceive('sendEmailVerificationNotification');

        $listener = new SendEmailVerificationNotification;

        $listener->handle(new Registered($user));
    }

    /**
     * @return void
     */
    public function testHasVerifiedEmailAsTrue()
    {
        $user = $this->getMockBuilder(MustVerifyEmail::class)->getMock();
        $user->method('hasVerifiedEmail')->willReturn(true);
        $user->expects($this->never())->method('sendEmailVerificationNotification');

        $listener = new SendEmailVerificationNotification;

        $listener->handle(new Registered($user));
    }
}
