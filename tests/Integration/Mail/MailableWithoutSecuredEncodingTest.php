<?php

namespace Kasi\Tests\Integration\Mail;

use Kasi\Foundation\Auth\User;
use Kasi\Foundation\Testing\LazilyRefreshDatabase;
use Kasi\Mail\Mailable;
use Kasi\Mail\Markdown;
use Orchestra\Testbench\Attributes\WithMigration;
use Orchestra\Testbench\Factories\UserFactory;
use PHPUnit\Framework\Attributes\DataProvider;

class MailableWithoutSecuredEncodingTest extends MailableTestCase
{
    use LazilyRefreshDatabase;

    /** {@inheritdoc} */
    #[\Override]
    protected function defineEnvironment($app)
    {
        parent::defineEnvironment($app);

        Markdown::withoutSecuredEncoding();
    }

    #[WithMigration]
    #[DataProvider('markdownEncodedTemplateDataProvider')]
    public function testItCanAssertMarkdownEncodedStringUsingTemplate($given, $expected)
    {
        $user = UserFactory::new()->create([
            'name' => $given,
        ]);

        $mailable = new class($user) extends Mailable
        {
            public $theme = 'taylor';

            public function __construct(public User $user)
            {
                //
            }

            public function build()
            {
                return $this->markdown('message-with-template');
            }
        };

        $mailable->assertSeeInHtml($expected, false);
    }

    #[WithMigration]
    #[DataProvider('markdownEncodedTemplateDataProvider')]
    public function testItCanAssertMarkdownEncodedStringUsingTemplateWithTable($given, $expected)
    {
        $user = UserFactory::new()->create([
            'name' => $given,
        ]);

        $mailable = new class($user) extends Mailable
        {
            public $theme = 'taylor';

            public function __construct(public User $user)
            {
                //
            }

            public function build()
            {
                return $this->markdown('table-with-template');
            }
        };

        $mailable->assertSeeInHtml($expected, false);
        $mailable->assertSeeInHtml('<p>This is a subcopy</p>', false);
        $mailable->assertSeeInHtml(<<<'TABLE'
<table>
<thead>
<tr>
<th>Kasi</th>
<th align="center">Table</th>
<th align="right">Example</th>
</tr>
</thead>
<tbody>
<tr>
<td>Col 2 is</td>
<td align="center">Centered</td>
<td align="right">$10</td>
</tr>
<tr>
<td>Col 3 is</td>
<td align="center">Right-Aligned</td>
<td align="right">$20</td>
</tr>
</tbody>
</table>
TABLE, false);
    }

    public static function markdownEncodedTemplateDataProvider()
    {
        yield ['[Kasi](https://kasi.com)', '<p><em>Hi</em> <a href="https://kasi.com">Kasi</a></p>'];

        yield [
            '![Welcome to Kasi](https://kasi.com/assets/img/welcome/background.svg)',
            '<p><em>Hi</em> <img src="https://kasi.com/assets/img/welcome/background.svg" alt="Welcome to Kasi"></p>',
        ];

        yield [
            'Visit https://kasi.com/docs to browse the documentation',
            '<em>Hi</em> Visit https://kasi.com/docs to browse the documentation',
        ];

        yield [
            'Visit <https://kasi.com/docs> to browse the documentation',
            '<em>Hi</em> Visit &lt;https://kasi.com/docs&gt; to browse the documentation',
        ];

        yield [
            'Visit <span>https://kasi.com/docs</span> to browse the documentation',
            '<em>Hi</em> Visit &lt;span&gt;https://kasi.com/docs&lt;/span&gt; to browse the documentation',
        ];
    }
}
