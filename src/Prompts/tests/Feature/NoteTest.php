<?php

use Kasi\Prompts\Prompt;

use function Kasi\Prompts\note;

it('renders a note', function () {
    Prompt::fake();

    note('Hello, World!');

    Prompt::assertOutputContains('Hello, World!');
});
