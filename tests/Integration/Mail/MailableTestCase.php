<?php

namespace Kasi\Tests\Integration\Mail;

use Kasi\Mail\Mailable;
use Kasi\Mail\Mailables\Content;
use Kasi\Mail\Mailables\Envelope;
use Orchestra\Testbench\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

abstract class MailableTestCase extends TestCase
{
    /** {@inheritdoc} */
    #[\Override]
    protected function defineEnvironment($app)
    {
        $app['view']->addLocation(__DIR__.'/Fixtures');
    }

    #[DataProvider('markdownEncodedDataProvider')]
    public function testItCanAssertMarkdownEncodedString($given, $expected)
    {
        $mailable = new class($given) extends Mailable
        {
            public function __construct(public string $message)
            {
                //
            }

            public function envelope()
            {
                return new Envelope(
                    subject: 'My basic title',
                );
            }

            public function content()
            {
                return new Content(
                    markdown: 'message',
                );
            }
        };

        $mailable->assertSeeInHtml($expected, false);
    }

    public static function markdownEncodedDataProvider()
    {
        yield ['[Kasi](https://kasi.com)', 'My message is: [Kasi](https://kasi.com)'];

        yield [
            '![Welcome to Kasi](https://kasi.com/assets/img/welcome/background.svg)',
            'My message is: ![Welcome to Kasi](https://kasi.com/assets/img/welcome/background.svg)',
        ];

        yield [
            'Visit https://kasi.com/docs to browse the documentation',
            'My message is: Visit https://kasi.com/docs to browse the documentation',
        ];

        yield [
            'Visit <https://kasi.com/docs> to browse the documentation',
            'My message is: Visit &lt;https://kasi.com/docs&gt; to browse the documentation',
        ];

        yield [
            'Visit <span>https://kasi.com/docs</span> to browse the documentation',
            'My message is: Visit &lt;span&gt;https://kasi.com/docs&lt;/span&gt; to browse the documentation',
        ];
    }
}
