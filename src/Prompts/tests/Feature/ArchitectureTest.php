<?php

arch("Doesn't use collections")
    ->expect('Kasi\Prompts')
    ->not->toUse(['collect']);
