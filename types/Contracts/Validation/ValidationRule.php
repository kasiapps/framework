<?php

use Kasi\Contracts\Validation\ValidationRule;

use function PHPStan\Testing\assertType;

$class = new class implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        assertType('Closure(string, string|null=): Kasi\Translation\PotentiallyTranslatedString', $fail);
    }
};
