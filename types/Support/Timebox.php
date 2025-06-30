<?php

use Kasi\Support\Timebox;

use function PHPStan\Testing\assertType;

assertType('1', (new Timebox)->call(function ($timebox) {
    assertType('Kasi\Support\Timebox', $timebox);

    return 1;
}, 1));
