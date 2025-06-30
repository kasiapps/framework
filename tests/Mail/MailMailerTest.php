<?php

namespace Kasi\Tests\Mail;

use Kasi\Contracts\Events\Dispatcher;
use Kasi\Contracts\View\Factory;
use Kasi\Mail\Events\MessageSending;
use Kasi\Mail\Events\MessageSent;
use Kasi\Mail\Mailer;
use Kasi\Mail\Message;
use Kasi\Mail\Transport\ArrayTransport;
use Kasi\Support\HtmlString;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class MailMailerTest extends TestCase
{
    protected function tearDown(): void
    {
        unset($_SERVER['__mailer.test']);

        m::close();
    }

    public function testMailerSendSendsMessageWithProperViewContent()
    {
        $view = m::mock(Factory::class);
        $view->shouldReceive('make')->once()->andReturn($view);
        $view->shouldReceive('render')->once()->andReturn('rendered.view');

        $mailer = new Mailer('array', $view, new ArrayTransport);

        $sentMessage = $mailer->send('foo', ['data'], function (Message $message) {
            $message->to('taylor@kasi.com')->from('hello@kasi.com');
        });

        $this->assertStringContainsString('rendered.view', $sentMessage->toString());
    }

    public function testMailerSendSendsMessageWithCcAndBccRecipients()
    {
        $view = m::mock(Factory::class);
        $view->shouldReceive('make')->once()->andReturn($view);
        $view->shouldReceive('render')->once()->andReturn('rendered.view');

        $mailer = new Mailer('array', $view, new ArrayTransport);

        $sentMessage = $mailer->send('foo', ['data'], function (Message $message) {
            $message->to('taylor@kasi.com')
                ->cc('dries@kasi.com')
                ->bcc('james@kasi.com')
                ->from('hello@kasi.com');
        });

        $recipients = collect($sentMessage->getEnvelope()->getRecipients())->map(function ($recipient) {
            return $recipient->getAddress();
        });

        $this->assertStringContainsString('rendered.view', $sentMessage->toString());
        $this->assertStringContainsString('dries@kasi.com', $sentMessage->toString());
        $this->assertStringNotContainsString('james@kasi.com', $sentMessage->toString());
        $this->assertTrue($recipients->contains('james@kasi.com'));
    }

    public function testMailerSendSendsMessageWithProperViewContentUsingHtmlStrings()
    {
        $view = m::mock(Factory::class);
        $view->shouldReceive('render')->never();

        $mailer = new Mailer('array', $view, new ArrayTransport);

        $sentMessage = $mailer->send(
            ['html' => new HtmlString('<p>Hello Kasi</p>'), 'text' => new HtmlString('Hello World')],
            ['data'],
            function (Message $message) {
                $message->to('taylor@kasi.com')->from('hello@kasi.com');
            }
        );

        $this->assertStringContainsString('<p>Hello Kasi</p>', $sentMessage->toString());
        $this->assertStringContainsString('Hello World', $sentMessage->toString());
    }

    public function testMailerSendSendsMessageWithProperViewContentUsingStringCallbacks()
    {
        $view = m::mock(Factory::class);
        $view->shouldReceive('render')->never();

        $mailer = new Mailer('array', $view, new ArrayTransport);

        $sentMessage = $mailer->send(
            [
                'html' => function ($data) {
                    $this->assertInstanceOf(Message::class, $data['message']);

                    return new HtmlString('<p>Hello Kasi</p>');
                },
                'text' => function ($data) {
                    $this->assertInstanceOf(Message::class, $data['message']);

                    return new HtmlString('Hello World');
                },
            ],
            [],
            function (Message $message) {
                $message->to('taylor@kasi.com')->from('hello@kasi.com');
            }
        );

        $this->assertStringContainsString('<p>Hello Kasi</p>', $sentMessage->toString());
        $this->assertStringContainsString('Hello World', $sentMessage->toString());
    }

    public function testMailerSendSendsMessageWithProperViewContentUsingHtmlMethod()
    {
        $view = m::mock(Factory::class);
        $view->shouldReceive('render')->never();

        $mailer = new Mailer('array', $view, new ArrayTransport);

        $sentMessage = $mailer->html('<p>Hello World</p>', function (Message $message) {
            $message->to('taylor@kasi.com')->from('hello@kasi.com');
        });

        $this->assertStringContainsString('<p>Hello World</p>', $sentMessage->toString());
    }

    public function testMailerSendSendsMessageWithProperPlainViewContent()
    {
        $view = m::mock(Factory::class);
        $view->shouldReceive('make')->twice()->andReturn($view);
        $view->shouldReceive('render')->once()->andReturn('rendered.view');
        $view->shouldReceive('render')->once()->andReturn('rendered.plain');

        $mailer = new Mailer('array', $view, new ArrayTransport);

        $sentMessage = $mailer->send(['foo', 'bar'], ['data'], function (Message $message) {
            $message->to('taylor@kasi.com')->from('hello@kasi.com');
        });

        $expected = <<<Text
        Content-Type: text/html; charset=utf-8\r
        Content-Transfer-Encoding: quoted-printable\r
        \r
        rendered.view
        Text;

        $this->assertStringContainsString($expected, $sentMessage->toString());

        $expected = <<<Text
        Content-Type: text/plain; charset=utf-8\r
        Content-Transfer-Encoding: quoted-printable\r
        \r
        rendered.plain
        Text;

        $this->assertStringContainsString($expected, $sentMessage->toString());
    }

    public function testMailerSendSendsMessageWithProperPlainViewContentWhenExplicit()
    {
        $view = m::mock(Factory::class);
        $view->shouldReceive('make')->twice()->andReturn($view);
        $view->shouldReceive('render')->once()->andReturn('rendered.view');
        $view->shouldReceive('render')->once()->andReturn('rendered.plain');

        $mailer = new Mailer('array', $view, new ArrayTransport);

        $sentMessage = $mailer->send(['html' => 'foo', 'text' => 'bar'], ['data'], function (Message $message) {
            $message->to('taylor@kasi.com')->from('hello@kasi.com');
        });

        $expected = <<<Text
        Content-Type: text/html; charset=utf-8\r
        Content-Transfer-Encoding: quoted-printable\r
        \r
        rendered.view
        Text;

        $this->assertStringContainsString($expected, $sentMessage->toString());

        $expected = <<<Text
        Content-Type: text/plain; charset=utf-8\r
        Content-Transfer-Encoding: quoted-printable\r
        \r
        rendered.plain
        Text;

        $this->assertStringContainsString($expected, $sentMessage->toString());
    }

    public function testToAllowsEmailAndName()
    {
        $view = m::mock(Factory::class);
        $view->shouldReceive('make')->once()->andReturn($view);
        $view->shouldReceive('render')->once()->andReturn('rendered.view');
        $mailer = new Mailer('array', $view, new ArrayTransport);

        $sentMessage = $mailer->to('taylor@kasi.com', 'Taylor Otwell')->send(new TestMail());

        $recipients = $sentMessage->getEnvelope()->getRecipients();
        $this->assertCount(1, $recipients);
        $this->assertSame('taylor@kasi.com', $recipients[0]->getAddress());
        $this->assertSame('Taylor Otwell', $recipients[0]->getName());
    }

    public function testGlobalFromIsRespectedOnAllMessages()
    {
        $view = m::mock(Factory::class);
        $view->shouldReceive('make')->once()->andReturn($view);
        $view->shouldReceive('render')->once()->andReturn('rendered.view');
        $mailer = new Mailer('array', $view, new ArrayTransport);
        $mailer->alwaysFrom('hello@kasi.com');

        $sentMessage = $mailer->send('foo', ['data'], function (Message $message) {
            $message->to('taylor@kasi.com');
        });

        $this->assertSame('taylor@kasi.com', $sentMessage->getEnvelope()->getRecipients()[0]->getAddress());
        $this->assertSame('hello@kasi.com', $sentMessage->getEnvelope()->getSender()->getAddress());
    }

    public function testGlobalReplyToIsRespectedOnAllMessages()
    {
        $view = m::mock(Factory::class);
        $view->shouldReceive('make')->once()->andReturn($view);
        $view->shouldReceive('render')->once()->andReturn('rendered.view');
        $mailer = new Mailer('array', $view, new ArrayTransport);
        $mailer->alwaysReplyTo('taylor@kasi.com', 'Taylor Otwell');

        $sentMessage = $mailer->send('foo', ['data'], function (Message $message) {
            $message->to('dries@kasi.com')->from('hello@kasi.com');
        });

        $this->assertSame('dries@kasi.com', $sentMessage->getEnvelope()->getRecipients()[0]->getAddress());
        $this->assertStringContainsString('Reply-To: Taylor Otwell <taylor@kasi.com>', $sentMessage->toString());
    }

    public function testGlobalToIsRespectedOnAllMessages()
    {
        $view = m::mock(Factory::class);
        $view->shouldReceive('make')->once()->andReturn($view);
        $view->shouldReceive('render')->once()->andReturn('rendered.view');
        $mailer = new Mailer('array', $view, new ArrayTransport);
        $mailer->alwaysTo('taylor@kasi.com', 'Taylor Otwell');

        $sentMessage = $mailer->send('foo', ['data'], function (Message $message) {
            $message->from('hello@kasi.com');
            $message->to('nuno@kasi.com');
            $message->cc('dries@kasi.com');
            $message->bcc('james@kasi.com');
        });

        $recipients = collect($sentMessage->getEnvelope()->getRecipients())->map(function ($recipient) {
            return $recipient->getAddress();
        });

        $this->assertSame('taylor@kasi.com', $sentMessage->getEnvelope()->getRecipients()[0]->getAddress());
        $this->assertDoesNotMatchRegularExpression('/^To: nuno@kasi.com/m', $sentMessage->toString());
        $this->assertDoesNotMatchRegularExpression('/^Cc: dries@kasi.com/m', $sentMessage->toString());
        $this->assertMatchesRegularExpression('/^X-To: nuno@kasi.com/m', $sentMessage->toString());
        $this->assertMatchesRegularExpression('/^X-Cc: dries@kasi.com/m', $sentMessage->toString());
        $this->assertMatchesRegularExpression('/^X-Bcc: james@kasi.com/m', $sentMessage->toString());
        $this->assertFalse($recipients->contains('nuno@kasi.com'));
        $this->assertFalse($recipients->contains('dries@kasi.com'));
        $this->assertFalse($recipients->contains('james@kasi.com'));
    }

    public function testGlobalReturnPathIsRespectedOnAllMessages()
    {
        $view = m::mock(Factory::class);
        $view->shouldReceive('make')->once()->andReturn($view);
        $view->shouldReceive('render')->once()->andReturn('rendered.view');

        $mailer = new Mailer('array', $view, new ArrayTransport);
        $mailer->alwaysReturnPath('taylorotwell@gmail.com');

        $sentMessage = $mailer->send('foo', ['data'], function (Message $message) {
            $message->to('taylor@kasi.com')->from('hello@kasi.com');
        });

        $this->assertStringContainsString('Return-Path: <taylorotwell@gmail.com>', $sentMessage->toString());
    }

    public function testEventsAreDispatched()
    {
        $view = m::mock(Factory::class);
        $view->shouldReceive('make')->once()->andReturn($view);
        $view->shouldReceive('render')->once()->andReturn('rendered.view');

        $events = m::mock(Dispatcher::class);
        $events->shouldReceive('until')->once()->with(m::type(MessageSending::class));
        $events->shouldReceive('dispatch')->once()->with(m::type(MessageSent::class));

        $mailer = new Mailer('array', $view, new ArrayTransport, $events);

        $mailer->send('foo', ['data'], function (Message $message) {
            $message->to('taylor@kasi.com')->from('hello@kasi.com');
        });
    }

    public function testMacroable()
    {
        Mailer::macro('foo', function () {
            return 'bar';
        });

        $mailer = new Mailer('array', m::mock(Factory::class), new ArrayTransport);

        $this->assertSame(
            'bar', $mailer->foo()
        );
    }
}

class TestMail extends \Kasi\Mail\Mailable
{
    public function build()
    {
        return $this->view('view')
            ->from('hello@kasi.com');
    }
}
