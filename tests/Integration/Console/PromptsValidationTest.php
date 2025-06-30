<?php

namespace Kasi\Tests\Integration\Console;

use Kasi\Console\Command;
use Kasi\Contracts\Console\Kernel;
use Orchestra\Testbench\TestCase;

use function Kasi\Prompts\text;

class PromptsValidationTest extends TestCase
{
    protected function defineEnvironment($app)
    {
        $app[Kernel::class]->registerCommand(new DummyPromptsValidationCommand());
        $app[Kernel::class]->registerCommand(new DummyPromptsWithKasiRulesCommand());
        $app[Kernel::class]->registerCommand(new DummyPromptsWithKasiRulesMessagesAndAttributesCommand());
        $app[Kernel::class]->registerCommand(new DummyPromptsWithKasiRulesCommandWithInlineMessagesAndAttributesCommand());
    }

    public function testValidationForPrompts()
    {
        $this
            ->artisan(DummyPromptsValidationCommand::class)
            ->expectsQuestion('What is your name?', '')
            ->expectsOutputToContain('Required!');
    }

    public function testValidationWithKasiRulesAndNoCustomization()
    {
        $this
            ->artisan(DummyPromptsWithKasiRulesCommand::class)
            ->expectsQuestion('What is your name?', '')
            ->expectsOutputToContain('The answer field is required.');
    }

    public function testValidationWithKasiRulesInlineMessagesAndAttributes()
    {
        $this
            ->artisan(DummyPromptsWithKasiRulesCommandWithInlineMessagesAndAttributesCommand::class)
            ->expectsQuestion('What is your name?', '')
            ->expectsOutputToContain('Your full name is mandatory.');
    }

    public function testValidationWithKasiRulesMessagesAndAttributes()
    {
        $this
            ->artisan(DummyPromptsWithKasiRulesMessagesAndAttributesCommand::class)
            ->expectsQuestion('What is your name?', '')
            ->expectsOutputToContain('Your full name is mandatory.');
    }
}

class DummyPromptsValidationCommand extends Command
{
    protected $signature = 'prompts-validation-test';

    public function handle()
    {
        text('What is your name?', validate: fn ($value) => $value == '' ? 'Required!' : null);
    }
}

class DummyPromptsWithKasiRulesCommand extends Command
{
    protected $signature = 'prompts-kasi-rules-test';

    public function handle()
    {
        text('What is your name?', validate: 'required');
    }
}

class DummyPromptsWithKasiRulesCommandWithInlineMessagesAndAttributesCommand extends Command
{
    protected $signature = 'prompts-kasi-rules-inline-test';

    public function handle()
    {
        text('What is your name?', validate: literal(
            rules: ['name' => 'required'],
            messages: ['name.required' => 'Your :attribute is mandatory.'],
            attributes: ['name' => 'full name'],
        ));
    }
}

class DummyPromptsWithKasiRulesMessagesAndAttributesCommand extends Command
{
    protected $signature = 'prompts-kasi-rules-messages-attributes-test';

    public function handle()
    {
        text('What is your name?', validate: ['name' => 'required']);
    }

    protected function validationMessages()
    {
        return ['name.required' => 'Your :attribute is mandatory.'];
    }

    protected function validationAttributes()
    {
        return ['name' => 'full name'];
    }
}
