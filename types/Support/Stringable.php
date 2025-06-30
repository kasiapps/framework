<?php

use Kasi\Support\Stringable;

use function PHPStan\Testing\assertType;

$stringable = new Stringable();

assertType('Kasi\Support\Collection<int, string>', $stringable->explode(''));

assertType('Kasi\Support\Collection<int, string>', $stringable->split(1));

assertType('Kasi\Support\Collection<int, string>', $stringable->ucsplit());
