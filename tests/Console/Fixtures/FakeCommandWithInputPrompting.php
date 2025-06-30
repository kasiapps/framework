<?php

namespace Kasi\Tests\Console\Fixtures;

use Kasi\Console\Command;
use Kasi\Contracts\Console\PromptsForMissingInput;
use Kasi\Prompts\Prompt;
use Kasi\Prompts\TextPrompt;
use Symfony\Component\Console\Input\InputInterface;

class FakeCommandWithInputPrompting extends Command implements PromptsForMissingInput
{
    protected $signature = 'fake-command-for-testing {name : An argument}';

    public $prompted = false;

    protected function configurePrompts(InputInterface $input)
    {
        Prompt::interactive(true);
        Prompt::fallbackWhen(true);

        TextPrompt::fallbackUsing(function () {
            $this->prompted = true;

            return 'foo';
        });
    }

    public function handle(): int
    {
        return self::SUCCESS;
    }
}
