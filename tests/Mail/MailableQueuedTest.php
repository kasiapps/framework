<?php

namespace Kasi\Tests\Mail;

use Kasi\Bus\Queueable;
use Kasi\Container\Container;
use Kasi\Contracts\Queue\ShouldQueue;
use Kasi\Contracts\View\Factory;
use Kasi\Filesystem\Filesystem;
use Kasi\Filesystem\FilesystemManager;
use Kasi\Foundation\Application;
use Kasi\Mail\Mailable;
use Kasi\Mail\Mailer;
use Kasi\Mail\SendQueuedMailable;
use Kasi\Support\Testing\Fakes\QueueFake;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mailer\Transport\TransportInterface;

class MailableQueuedTest extends TestCase
{
    protected function tearDown(): void
    {
        m::close();
    }

    public function testQueuedMailableSent()
    {
        $queueFake = new QueueFake(new Application);
        $mailer = $this->getMockBuilder(Mailer::class)
            ->setConstructorArgs($this->getMocks())
            ->onlyMethods(['createMessage', 'to'])
            ->getMock();
        $mailer->setQueue($queueFake);
        $mailable = new MailableQueueableStub;
        $queueFake->assertNothingPushed();
        $mailer->send($mailable);
        $queueFake->assertPushedOn(null, SendQueuedMailable::class);
    }

    public function testQueuedMailableWithAttachmentSent()
    {
        $queueFake = new QueueFake(new Application);
        $mailer = $this->getMockBuilder(Mailer::class)
            ->setConstructorArgs($this->getMocks())
            ->onlyMethods(['createMessage'])
            ->getMock();
        $mailer->setQueue($queueFake);
        $mailable = new MailableQueueableStub;
        $attachmentOption = ['mime' => 'image/jpeg', 'as' => 'bar.jpg'];
        $mailable->attach('foo.jpg', $attachmentOption);
        $this->assertIsArray($mailable->attachments);
        $this->assertCount(1, $mailable->attachments);
        $this->assertEquals($mailable->attachments[0]['options'], $attachmentOption);
        $queueFake->assertNothingPushed();
        $mailer->send($mailable);
        $queueFake->assertPushedOn(null, SendQueuedMailable::class);
    }

    public function testQueuedMailableWithAttachmentFromDiskSent()
    {
        $app = new Application;
        $container = Container::getInstance();
        $this->getMockBuilder(Filesystem::class)
            ->getMock();
        $filesystemFactory = $this->getMockBuilder(FilesystemManager::class)
            ->setConstructorArgs([$app])
            ->getMock();
        $container->instance('filesystem', $filesystemFactory);
        $queueFake = new QueueFake($app);
        $mailer = $this->getMockBuilder(Mailer::class)
            ->setConstructorArgs($this->getMocks())
            ->onlyMethods(['createMessage'])
            ->getMock();
        $mailer->setQueue($queueFake);
        $mailable = new MailableQueueableStub;
        $attachmentOption = ['mime' => 'image/jpeg', 'as' => 'bar.jpg'];

        $mailable->attachFromStorage('/', 'foo.jpg', $attachmentOption);

        $this->assertIsArray($mailable->diskAttachments);
        $this->assertCount(1, $mailable->diskAttachments);
        $this->assertEquals($mailable->diskAttachments[0]['options'], $attachmentOption);

        $queueFake->assertNothingPushed();
        $mailer->send($mailable);
        $queueFake->assertPushedOn(null, SendQueuedMailable::class);
    }

    protected function getMocks()
    {
        return ['smtp', m::mock(Factory::class), m::mock(TransportInterface::class)];
    }
}

class MailableQueueableStub extends Mailable implements ShouldQueue
{
    use Queueable;

    public function build(): self
    {
        $this
            ->subject('lorem ipsum')
            ->html('foo bar baz')
            ->to('foo@example.tld');

        return $this;
    }
}
