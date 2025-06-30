<?php

namespace Kasi\Tests\Integration\Mail;

use Kasi\Mail\Markdown;
use Kasi\Support\EncodedHtmlString;
use Kasi\Support\HtmlString;
use Orchestra\Testbench\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class MarkdownParserTest extends TestCase
{
    /** {@inheritdoc} */
    #[\Override]
    protected function tearDown(): void
    {
        Markdown::flushState();
        EncodedHtmlString::flushState();

        parent::tearDown();
    }

    #[DataProvider('markdownDataProvider')]
    public function testItCanParseMarkdownString($given, $expected)
    {
        tap(Markdown::parse($given), function ($html) use ($expected) {
            $this->assertInstanceOf(HtmlString::class, $html);

            $this->assertStringEqualsStringIgnoringLineEndings($expected.PHP_EOL, (string) $html);
            $this->assertSame((string) $html, (string) $html->toHtml());
        });
    }

    #[DataProvider('markdownEncodedDataProvider')]
    public function testItCanParseMarkdownEncodedString($given, $expected)
    {
        tap(Markdown::parse($given, encoded: true), function ($html) use ($expected) {
            $this->assertInstanceOf(HtmlString::class, $html);

            $this->assertStringEqualsStringIgnoringLineEndings($expected.PHP_EOL, (string) $html);
        });
    }

    public static function markdownDataProvider()
    {
        yield ['[Kasi](https://kasi.com)', '<p><a href="https://kasi.com">Kasi</a></p>'];
        yield ['\[Kasi](https://kasi.com)', '<p>[Kasi](https://kasi.com)</p>'];
        yield ['![Welcome to Kasi](https://kasi.com/assets/img/welcome/background.svg)', '<p><img src="https://kasi.com/assets/img/welcome/background.svg" alt="Welcome to Kasi" /></p>'];
        yield ['!\[Welcome to Kasi](https://kasi.com/assets/img/welcome/background.svg)', '<p>![Welcome to Kasi](https://kasi.com/assets/img/welcome/background.svg)</p>'];
        yield ['Visit https://kasi.com/docs to browse the documentation', '<p>Visit https://kasi.com/docs to browse the documentation</p>'];
        yield ['Visit <https://kasi.com/docs> to browse the documentation', '<p>Visit <a href="https://kasi.com/docs">https://kasi.com/docs</a> to browse the documentation</p>'];
        yield ['Visit <span>https://kasi.com/docs</span> to browse the documentation', '<p>Visit <span>https://kasi.com/docs</span> to browse the documentation</p>'];
    }

    public static function markdownEncodedDataProvider()
    {
        yield [new EncodedHtmlString('[Kasi](https://kasi.com)'), '<p>[Kasi](https://kasi.com)</p>'];

        yield [
            new EncodedHtmlString('![Welcome to Kasi](https://kasi.com/assets/img/welcome/background.svg)'),
            '<p>![Welcome to Kasi](https://kasi.com/assets/img/welcome/background.svg)</p>',
        ];

        yield [
            new EncodedHtmlString('Visit https://kasi.com/docs to browse the documentation'),
            '<p>Visit https://kasi.com/docs to browse the documentation</p>',
        ];

        yield [
            new EncodedHtmlString('Visit <https://kasi.com/docs> to browse the documentation'),
            '<p>Visit &lt;https://kasi.com/docs&gt; to browse the documentation</p>',
        ];

        yield [
            new EncodedHtmlString('Visit <span>https://kasi.com/docs</span> to browse the documentation'),
            '<p>Visit &lt;span&gt;https://kasi.com/docs&lt;/span&gt; to browse the documentation</p>',
        ];

        yield [
            new EncodedHtmlString(new HtmlString('Visit <span>https://kasi.com/docs</span> to browse the documentation')),
            '<p>Visit <span>https://kasi.com/docs</span> to browse the documentation</p>',
        ];

        yield [
            '![Welcome to Kasi](https://kasi.com/assets/img/welcome/background.svg)<br />'.new EncodedHtmlString('Visit <span>https://kasi.com/docs</span> to browse the documentation'),
            '<p><img src="https://kasi.com/assets/img/welcome/background.svg" alt="Welcome to Kasi" /><br />Visit &lt;span&gt;https://kasi.com/docs&lt;/span&gt; to browse the documentation</p>',
        ];
    }
}
