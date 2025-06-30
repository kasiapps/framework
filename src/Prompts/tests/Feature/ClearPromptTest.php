<?php

use Kasi\Prompts\Prompt;

use function Kasi\Prompts\clear;

it('clears', function () {
    Prompt::fake();

    clear();

    Prompt::assertOutputContains("\033[H\033[J");
});
