<?php

use function PHPStan\Testing\assertType;

assertType(
    'Kasi\Contracts\Database\Eloquent\CastsAttributes<Kasi\Database\Eloquent\Casts\ArrayObject<(int|string), mixed>, iterable>',
    \Kasi\Database\Eloquent\Casts\AsArrayObject::castUsing([]),
);

assertType(
    'Kasi\Contracts\Database\Eloquent\CastsAttributes<Kasi\Support\Collection<(int|string), mixed>, iterable>',
    \Kasi\Database\Eloquent\Casts\AsCollection::castUsing([]),
);

assertType(
    'Kasi\Contracts\Database\Eloquent\CastsAttributes<Kasi\Database\Eloquent\Casts\ArrayObject<(int|string), mixed>, iterable>',
    \Kasi\Database\Eloquent\Casts\AsEncryptedArrayObject::castUsing([]),
);

assertType(
    'Kasi\Contracts\Database\Eloquent\CastsAttributes<Kasi\Support\Collection<(int|string), mixed>, iterable>',
    \Kasi\Database\Eloquent\Casts\AsEncryptedCollection::castUsing([]),
);

assertType(
    'Kasi\Contracts\Database\Eloquent\CastsAttributes<Kasi\Database\Eloquent\Casts\ArrayObject<(int|string), UserType>, iterable<UserType>>',
    \Kasi\Database\Eloquent\Casts\AsEnumArrayObject::castUsing([\UserType::class]),
);

assertType(
    'Kasi\Contracts\Database\Eloquent\CastsAttributes<Kasi\Support\Collection<(int|string), UserType>, iterable<UserType>>',
    \Kasi\Database\Eloquent\Casts\AsEnumCollection::castUsing([\UserType::class]),
);

assertType(
    'Kasi\Contracts\Database\Eloquent\CastsAttributes<Kasi\Support\Stringable, string|Stringable>',
    \Kasi\Database\Eloquent\Casts\AsStringable::castUsing([]),
);
