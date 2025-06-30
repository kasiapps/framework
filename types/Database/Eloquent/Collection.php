<?php

use function PHPStan\Testing\assertType;

$collection = User::all();
assertType('Kasi\Database\Eloquent\Collection<int, User>', $collection);

assertType('User|null', $collection->find(1));
assertType("'string'|User", $collection->find(1, 'string'));
assertType('Kasi\Database\Eloquent\Collection<int, User>', $collection->find([1]));

assertType('Kasi\Database\Eloquent\Collection<int, User>', $collection->load('string'));
assertType('Kasi\Database\Eloquent\Collection<int, User>', $collection->load(['string']));
assertType('Kasi\Database\Eloquent\Collection<int, User>', $collection->load(['string' => ['foo' => fn ($q) => $q]]));
assertType('Kasi\Database\Eloquent\Collection<int, User>', $collection->load(['string' => function ($query) {
    // assertType('Kasi\Database\Eloquent\Relations\Relation<*,*,*>', $query);
}]));

assertType('Kasi\Database\Eloquent\Collection<int, User>', $collection->loadAggregate('string', 'string'));
assertType('Kasi\Database\Eloquent\Collection<int, User>', $collection->loadAggregate(['string'], 'string'));
assertType('Kasi\Database\Eloquent\Collection<int, User>', $collection->loadAggregate(['string' => ['foo' => fn ($q) => $q]], 'string'));
assertType('Kasi\Database\Eloquent\Collection<int, User>', $collection->loadAggregate(['string'], 'string', 'string'));
assertType('Kasi\Database\Eloquent\Collection<int, User>', $collection->loadAggregate(['string' => function ($query) {
    // assertType('Kasi\Database\Eloquent\Relations\Relation<*,*,*>', $query);
}], 'string', 'string'));

assertType('Kasi\Database\Eloquent\Collection<int, User>', $collection->loadCount('string'));
assertType('Kasi\Database\Eloquent\Collection<int, User>', $collection->loadCount(['string']));
assertType('Kasi\Database\Eloquent\Collection<int, User>', $collection->loadCount(['string' => ['foo' => fn ($q) => $q]]));
assertType('Kasi\Database\Eloquent\Collection<int, User>', $collection->loadCount(['string' => function ($query) {
    // assertType('Kasi\Database\Eloquent\Relations\Relation<*,*,*>', $query);
}]));

assertType('Kasi\Database\Eloquent\Collection<int, User>', $collection->loadMax('string', 'string'));
assertType('Kasi\Database\Eloquent\Collection<int, User>', $collection->loadMax(['string'], 'string'));
assertType('Kasi\Database\Eloquent\Collection<int, User>', $collection->loadMax(['string' => ['foo' => fn ($q) => $q]], 'string'));
assertType('Kasi\Database\Eloquent\Collection<int, User>', $collection->loadMax(['string' => function ($query) {
    // assertType('Kasi\Database\Eloquent\Relations\Relation<*,*,*>', $query);
}], 'string'));

assertType('Kasi\Database\Eloquent\Collection<int, User>', $collection->loadMin('string', 'string'));
assertType('Kasi\Database\Eloquent\Collection<int, User>', $collection->loadMin(['string'], 'string'));
assertType('Kasi\Database\Eloquent\Collection<int, User>', $collection->loadMin(['string' => ['foo' => fn ($q) => $q]], 'string'));
assertType('Kasi\Database\Eloquent\Collection<int, User>', $collection->loadMin(['string' => function ($query) {
    // assertType('Kasi\Database\Eloquent\Relations\Relation<*,*,*>', $query);
}], 'string'));

assertType('Kasi\Database\Eloquent\Collection<int, User>', $collection->loadSum('string', 'string'));
assertType('Kasi\Database\Eloquent\Collection<int, User>', $collection->loadSum(['string'], 'string'));
assertType('Kasi\Database\Eloquent\Collection<int, User>', $collection->loadSum(['string' => ['foo' => fn ($q) => $q]], 'string'));
assertType('Kasi\Database\Eloquent\Collection<int, User>', $collection->loadSum(['string' => function ($query) {
    // assertType('Kasi\Database\Eloquent\Relations\Relation<*,*,*>', $query);
}], 'string'));

assertType('Kasi\Database\Eloquent\Collection<int, User>', $collection->loadAvg('string', 'string'));
assertType('Kasi\Database\Eloquent\Collection<int, User>', $collection->loadAvg(['string'], 'string'));
assertType('Kasi\Database\Eloquent\Collection<int, User>', $collection->loadAvg(['string' => ['foo' => fn ($q) => $q]], 'string'));
assertType('Kasi\Database\Eloquent\Collection<int, User>', $collection->loadAvg(['string' => function ($query) {
    // assertType('Kasi\Database\Eloquent\Relations\Relation<*,*,*>', $query);
}], 'string'));

assertType('Kasi\Database\Eloquent\Collection<int, User>', $collection->loadExists('string'));
assertType('Kasi\Database\Eloquent\Collection<int, User>', $collection->loadExists(['string']));
assertType('Kasi\Database\Eloquent\Collection<int, User>', $collection->loadExists(['string' => ['foo' => fn ($q) => $q]]));
assertType('Kasi\Database\Eloquent\Collection<int, User>', $collection->loadExists(['string' => function ($query) {
    // assertType('Kasi\Database\Eloquent\Relations\Relation<*,*,*>', $query);
}]));

assertType('Kasi\Database\Eloquent\Collection<int, User>', $collection->loadMissing('string'));
assertType('Kasi\Database\Eloquent\Collection<int, User>', $collection->loadMissing(['string']));
assertType('Kasi\Database\Eloquent\Collection<int, User>', $collection->loadMissing(['string' => ['foo' => fn ($q) => $q]]));
assertType('Kasi\Database\Eloquent\Collection<int, User>', $collection->loadMissing(['string' => function ($query) {
    // assertType('Kasi\Database\Eloquent\Relations\Relation<*,*,*>', $query);
}]));

assertType('Kasi\Database\Eloquent\Collection<int, User>', $collection->loadMorph('string', ['string']));
assertType('Kasi\Database\Eloquent\Collection<int, User>', $collection->loadMorph('string', ['string' => ['foo' => fn ($q) => $q]]));
assertType('Kasi\Database\Eloquent\Collection<int, User>', $collection->loadMorph('string', ['string' => function ($query) {
    // assertType('Kasi\Database\Eloquent\Relations\Relation<*,*,*>', $query);
}]));

assertType('Kasi\Database\Eloquent\Collection<int, User>', $collection->loadMorphCount('string', ['string']));
assertType('Kasi\Database\Eloquent\Collection<int, User>', $collection->loadMorphCount('string', ['string' => ['foo' => fn ($q) => $q]]));
assertType('Kasi\Database\Eloquent\Collection<int, User>', $collection->loadMorphCount('string', ['string' => function ($query) {
    // assertType('Kasi\Database\Eloquent\Relations\Relation<*,*,*>', $query);
}]));

assertType('bool', $collection->contains(function ($user) {
    assertType('User', $user);

    return true;
}));
assertType('bool', $collection->contains('string', '=', 'string'));

assertType('array<int, (int|string)>', $collection->modelKeys());

assertType('Kasi\Database\Eloquent\Collection<int, User>', $collection->merge($collection));
assertType('Kasi\Database\Eloquent\Collection<int, User>', $collection->merge([new User]));

assertType(
    'Kasi\Support\Collection<int, User>',
    $collection->map(function ($user, $int) {
        assertType('User', $user);
        assertType('int', $int);

        return new User;
    })
);

assertType(
    'Kasi\Support\Collection<int, User>',
    $collection->mapWithKeys(function ($user, $int) {
        assertType('User', $user);
        assertType('int', $int);

        return [new User];
    })
);
assertType(
    'Kasi\Support\Collection<string, User>',
    $collection->mapWithKeys(function ($user, $int) {
        return ['string' => new User];
    })
);

assertType(
    'Kasi\Database\Eloquent\Collection<int, User>',
    $collection->fresh()
);
assertType(
    'Kasi\Database\Eloquent\Collection<int, User>',
    $collection->fresh('string')
);
assertType(
    'Kasi\Database\Eloquent\Collection<int, User>',
    $collection->fresh(['string'])
);

assertType('Kasi\Database\Eloquent\Collection<int, User>', $collection->diff($collection));
assertType('Kasi\Database\Eloquent\Collection<int, User>', $collection->diff([new User]));

assertType('Kasi\Database\Eloquent\Collection<int, User>', $collection->intersect($collection));
assertType('Kasi\Database\Eloquent\Collection<int, User>', $collection->intersect([new User]));

assertType('Kasi\Database\Eloquent\Collection<int, User>', $collection->unique());
assertType('Kasi\Database\Eloquent\Collection<int, User>', $collection->unique(function ($user, $int) {
    assertType('User', $user);
    assertType('int', $int);

    return $user->getTable();
}));
assertType('Kasi\Database\Eloquent\Collection<int, User>', $collection->unique('string'));

assertType('Kasi\Database\Eloquent\Collection<int, User>', $collection->only(null));
assertType('Kasi\Database\Eloquent\Collection<int, User>', $collection->only(['string']));

assertType('Kasi\Database\Eloquent\Collection<int, User>', $collection->except(null));
assertType('Kasi\Database\Eloquent\Collection<int, User>', $collection->except(['string']));

assertType('Kasi\Database\Eloquent\Collection<int, User>', $collection->makeHidden('string'));
assertType('Kasi\Database\Eloquent\Collection<int, User>', $collection->makeHidden(['string']));

assertType('Kasi\Database\Eloquent\Collection<int, User>', $collection->makeVisible('string'));
assertType('Kasi\Database\Eloquent\Collection<int, User>', $collection->makeVisible(['string']));

assertType('Kasi\Database\Eloquent\Collection<int, User>', $collection->append('string'));
assertType('Kasi\Database\Eloquent\Collection<int, User>', $collection->append(['string']));

assertType('Kasi\Database\Eloquent\Collection<int, User>', $collection->unique());
assertType('Kasi\Database\Eloquent\Collection<int, User>', $collection->uniqueStrict());

assertType('array<User>', $collection->getDictionary());
assertType('array<User>', $collection->getDictionary($collection));
assertType('array<User>', $collection->getDictionary([new User]));

assertType('Kasi\Support\Collection<(int|string), mixed>', $collection->pluck('string'));
assertType('Kasi\Support\Collection<(int|string), mixed>', $collection->pluck(['string']));

assertType('Kasi\Support\Collection<int, int>', $collection->keys());

assertType('Kasi\Support\Collection<int, Kasi\Support\Collection<int, int|User>>', $collection->zip([1]));
assertType('Kasi\Support\Collection<int, Kasi\Support\Collection<int, string|User>>', $collection->zip(['string']));

assertType('Kasi\Support\Collection<int, mixed>', $collection->collapse());

assertType('Kasi\Support\Collection<int, mixed>', $collection->flatten());
assertType('Kasi\Support\Collection<int, mixed>', $collection->flatten(4));

assertType('Kasi\Support\Collection<User, int>', $collection->flip());

assertType('Kasi\Support\Collection<int, int|User>', $collection->pad(2, 0));
assertType('Kasi\Support\Collection<int, string|User>', $collection->pad(2, 'string'));

assertType('array<int, mixed>', $collection->getQueueableIds());

assertType('array<int, string>', $collection->getQueueableRelations());

assertType('Kasi\Database\Eloquent\Builder<User>', $collection->toQuery());
