<?php

use Kasi\Contracts\Support\Arrayable;
use Kasi\Support\Collection;
use Kasi\Support\LazyCollection;

use function PHPStan\Testing\assertType;

/** @implements Arrayable<int, User> */
class Users implements Arrayable
{
    public function toArray(): array
    {
        return [new User()];
    }
}

$collection = new LazyCollection([new User]);
$arrayable = new Users();
/** @var iterable<int, int> $iterable */
$iterable = [1];
/** @var Traversable<int, string> $traversable */
$traversable = new ArrayIterator(['string']);
$generator = function () {
    yield new User();
};

assertType('Kasi\Support\LazyCollection<int, User>', $collection);

assertType("Kasi\Support\LazyCollection<int, 'string'>", new LazyCollection(['string']));
assertType('Kasi\Support\LazyCollection<string, User>', new LazyCollection(['string' => new User]));
assertType('Kasi\Support\LazyCollection<int, User>', new LazyCollection($arrayable));
assertType('Kasi\Support\LazyCollection<int, int>', new LazyCollection($iterable));
assertType('Kasi\Support\LazyCollection<int, string>', new LazyCollection($traversable));
assertType('Kasi\Support\LazyCollection<int, User>', new LazyCollection($generator));

assertType('Kasi\Support\LazyCollection<int, string>', LazyCollection::make(['string']));
assertType('Kasi\Support\LazyCollection<string, User>', LazyCollection::make(['string' => new User]));
assertType('Kasi\Support\LazyCollection<int, User>', LazyCollection::make($arrayable));
assertType('Kasi\Support\LazyCollection<int, int>', LazyCollection::make($iterable));
assertType('Kasi\Support\LazyCollection<int, string>', LazyCollection::make($traversable));
assertType('Kasi\Support\LazyCollection<int, User>', LazyCollection::make($generator));

assertType('Kasi\Support\LazyCollection<int, User>', $collection::times(10, function ($int) {
    // assertType('int', $int);

    return new User;
}));

assertType('Kasi\Support\LazyCollection<int, User>', $collection::times(10, function () {
    return new User;
}));

assertType('Kasi\Support\LazyCollection<int, User>', $collection->each(function ($user) {
    assertType('User', $user);
}));

assertType('Kasi\Support\LazyCollection<int, int>', $collection->range(1, 100));

assertType('Kasi\Support\LazyCollection<(int|string), string>', $collection->wrap('string'));
assertType('Kasi\Support\LazyCollection<(int|string), User>', $collection->wrap(new User));

assertType('Kasi\Support\LazyCollection<(int|string), string>', $collection->wrap(['string']));
assertType('Kasi\Support\LazyCollection<(int|string), User>', $collection->wrap(['string' => new User]));

assertType("array<0, 'string'>", $collection->unwrap(['string']));
assertType('array<int, User>', $collection->unwrap(
    $collection
));

assertType('Kasi\Support\LazyCollection<int, User>', $collection::empty());

assertType('float|int|null', $collection->average());
assertType('float|int|null', $collection->average('string'));
assertType('float|int|null', $collection->average(function ($user) {
    assertType('User', $user);

    return 1;
}));
assertType('float|int|null', $collection->average(function ($user) {
    assertType('User', $user);

    return 0.1;
}));

assertType('float|int|null', $collection->median());
assertType('float|int|null', $collection->median('string'));
assertType('float|int|null', $collection->median(['string']));

assertType('array<int, float|int>|null', $collection->mode());
assertType('array<int, float|int>|null', $collection->mode('string'));
assertType('array<int, float|int>|null', $collection->mode(['string']));

assertType('Kasi\Support\LazyCollection<int, mixed>', $collection->collapse());

assertType('bool', $collection->some(function ($user) {
    assertType('User', $user);

    return true;
}));
assertType('bool', $collection::make(['string'])->some('string', '=', 'string'));

assertType('bool', $collection->containsStrict(function ($user) {
    assertType('User', $user);

    return true;
}));
assertType('bool', $collection::make(['string'])->containsStrict('string', 'string'));

assertType('float|int|null', $collection->avg());
assertType('float|int|null', $collection->avg('string'));
assertType('float|int|null', $collection->avg(function ($user) {
    assertType('User', $user);

    return 1;
}));
assertType('float|int|null', $collection->avg(function ($user) {
    assertType('User', $user);

    return 0.1;
}));

assertType('bool', $collection->contains(function ($user) {
    assertType('User', $user);

    return true;
}));
assertType('bool', $collection->contains(function ($user, $int) {
    assertType('int', $int);
    assertType('User', $user);

    return true;
}));
assertType('bool', $collection::make(['string'])->contains('string', '=', 'string'));

assertType('Kasi\Support\LazyCollection<int, array<int, string|User>>', $collection->crossJoin($collection::make(['string'])));
assertType('Kasi\Support\LazyCollection<int, array<int, int|User>>', $collection->crossJoin([1, 2]));

assertType('Kasi\Support\LazyCollection<int, int>', $collection::make([3, 4])->diff([1, 2]));
assertType('Kasi\Support\LazyCollection<int, string>', $collection::make(['string-1'])->diff(['string-2']));

assertType('Kasi\Support\LazyCollection<int, int>', $collection::make([3, 4])->diffUsing([1, 2], function ($intA, $intB) {
    assertType('int', $intA);
    assertType('int', $intB);

    return -1;
}));
assertType('Kasi\Support\LazyCollection<int, string>', $collection::make(['string-1'])->diffUsing(['string-2'], function ($stringA, $stringB) {
    assertType('string', $stringA);
    assertType('string', $stringB);

    return -1;
}));

assertType('Kasi\Support\LazyCollection<int, int>', $collection::make([3, 4])->diffAssoc([1, 2]));
assertType('Kasi\Support\LazyCollection<string, string>', $collection::make(['string' => 'string'])->diffAssoc(['string' => 'string']));

assertType('Kasi\Support\LazyCollection<int, int>', $collection::make([3, 4])->diffAssocUsing([1, 2], function ($intA, $intB) {
    assertType('int', $intA);
    assertType('int', $intB);

    return -1;
}));
assertType('Kasi\Support\LazyCollection<int, string>', $collection::make(['string-1'])->diffAssocUsing(['string-2'], function ($intA, $intB) {
    assertType('int', $intA);
    assertType('int', $intB);

    return -1;
}));

assertType('Kasi\Support\LazyCollection<int, int>', $collection::make([3, 4])->diffKeys([1, 2]));
assertType('Kasi\Support\LazyCollection<string, string>', $collection::make(['string' => 'string'])->diffKeys(['string' => 'string']));

assertType('Kasi\Support\LazyCollection<int, int>', $collection::make([3, 4])->diffKeysUsing([1, 2], function ($intA, $intB) {
    assertType('int', $intA);
    assertType('int', $intB);

    return -1;
}));
assertType('Kasi\Support\LazyCollection<int, string>', $collection::make(['string-1'])->diffKeysUsing(['string-2'], function ($intA, $intB) {
    assertType('int', $intA);
    assertType('int', $intB);

    return -1;
}));

assertType('Kasi\Support\LazyCollection<string, string>', $collection::make(['string' => 'string'])
    ->duplicates());
assertType('Kasi\Support\LazyCollection<int, User>', $collection->duplicates('name', true));
assertType('Kasi\Support\LazyCollection<int, int|string>', $collection::make([3, 'string'])
    ->duplicates(function ($intOrString) {
        assertType('int|string', $intOrString);

        return true;
    }));

assertType('Kasi\Support\LazyCollection<string, string>', $collection::make(['string' => 'string'])
    ->duplicatesStrict());
assertType('Kasi\Support\LazyCollection<int, User>', $collection->duplicatesStrict('name'));
assertType('Kasi\Support\LazyCollection<int, int|string>', $collection::make([3, 'string'])
    ->duplicatesStrict(function ($intOrString) {
        assertType('int|string', $intOrString);

        return true;
    }));

assertType('Kasi\Support\LazyCollection<int, User>', $collection->each(function ($user) {
    assertType('User', $user);

    return null;
}));
assertType('Kasi\Support\LazyCollection<int, User>', $collection->each(function ($user) {
    assertType('User', $user);
}));

assertType('Kasi\Support\LazyCollection<int, array{string}>', $collection::make([['string']])
    ->eachSpread(function ($int, $string) {
        // assertType('int', $int);
        // assertType('int', $string);

        return null;
    }));
assertType('Kasi\Support\LazyCollection<int, array{int, string}>', $collection::make([[1, 'string']])
    ->eachSpread(function ($int, $string) {
        // assertType('int', $int);
        // assertType('int', $string);
    }));

assertType('bool', $collection->every(function ($user, $int) {
    assertType('int', $int);
    assertType('User', $user);

    return true;
}));
assertType('bool', $collection::make(['string'])->every('string', '=', 'string'));

assertType('Kasi\Support\LazyCollection<string, string>', $collection::make(['string' => 'string'])->except(['string']));
assertType('Kasi\Support\LazyCollection<int, User>', $collection->except([1]));
assertType('Kasi\Support\LazyCollection<int, string>', $collection::make(['string'])
    ->except([1]));

assertType('Kasi\Support\LazyCollection<int, User>', $collection->filter());
assertType('Kasi\Support\LazyCollection<int, User>', $collection->filter(function ($user) {
    assertType('User', $user);

    return true;
}));

assertType('Kasi\Support\LazyCollection<int, User>', $collection->filter());
assertType('Kasi\Support\LazyCollection<int, User>', $collection->filter(function ($user) {
    assertType('User', $user);

    return true;
}));

assertType('Kasi\Support\LazyCollection<int, User>|true', $collection->when(true, function ($collection) {
    assertType('Kasi\Support\LazyCollection<int, User>', $collection);

    return true;
}));
assertType('Kasi\Support\LazyCollection<int, User>|null', $collection->when(true, function ($collection) {
    assertType('Kasi\Support\LazyCollection<int, User>', $collection);
}));
assertType("'string'|Kasi\Support\LazyCollection<int, User>", $collection->when(true, function ($collection) {
    assertType('Kasi\Support\LazyCollection<int, User>', $collection);

    return 'string';
}));

assertType('Kasi\Support\LazyCollection<int, User>|true', $collection->whenEmpty(function ($collection) {
    assertType('Kasi\Support\LazyCollection<int, User>', $collection);

    return true;
}));
assertType('Kasi\Support\LazyCollection<int, User>|null', $collection->whenEmpty(function ($collection) {
    assertType('Kasi\Support\LazyCollection<int, User>', $collection);
}));
assertType("'string'|Kasi\Support\LazyCollection<int, User>", $collection->whenEmpty(function ($collection) {
    assertType('Kasi\Support\LazyCollection<int, User>', $collection);

    return 'string';
}));

assertType('Kasi\Support\LazyCollection<int, User>|true', $collection->whenNotEmpty(function ($collection) {
    assertType('Kasi\Support\LazyCollection<int, User>', $collection);

    return true;
}));
assertType('Kasi\Support\LazyCollection<int, User>|null', $collection->whenNotEmpty(function ($collection) {
    assertType('Kasi\Support\LazyCollection<int, User>', $collection);
}));
assertType("'string'|Kasi\Support\LazyCollection<int, User>", $collection->whenNotEmpty(function ($collection) {
    assertType('Kasi\Support\LazyCollection<int, User>', $collection);

    return 'string';
}));

assertType('Kasi\Support\LazyCollection<int, User>|true', $collection->unless(true, function ($collection) {
    assertType('Kasi\Support\LazyCollection<int, User>', $collection);

    return true;
}));
assertType('Kasi\Support\LazyCollection<int, User>|null', $collection->unless(true, function ($collection) {
    assertType('Kasi\Support\LazyCollection<int, User>', $collection);
}));
assertType("'string'|Kasi\Support\LazyCollection<int, User>", $collection->unless(true, function ($collection) {
    assertType('Kasi\Support\LazyCollection<int, User>', $collection);

    return 'string';
}));

assertType('Kasi\Support\LazyCollection<int, User>|true', $collection->unlessEmpty(function ($collection) {
    assertType('Kasi\Support\LazyCollection<int, User>', $collection);

    return true;
}));
assertType('Kasi\Support\LazyCollection<int, User>|null', $collection->unlessEmpty(function ($collection) {
    assertType('Kasi\Support\LazyCollection<int, User>', $collection);
}));
assertType("'string'|Kasi\Support\LazyCollection<int, User>", $collection->unlessEmpty(function ($collection) {
    assertType('Kasi\Support\LazyCollection<int, User>', $collection);

    return 'string';
}));

assertType('Kasi\Support\LazyCollection<int, User>|true', $collection->unlessNotEmpty(function ($collection) {
    assertType('Kasi\Support\LazyCollection<int, User>', $collection);

    return true;
}));
assertType('Kasi\Support\LazyCollection<int, User>|null', $collection->unlessNotEmpty(function ($collection) {
    assertType('Kasi\Support\LazyCollection<int, User>', $collection);
}));
assertType("'string'|Kasi\Support\LazyCollection<int, User>", $collection->unlessNotEmpty(function ($collection) {
    assertType('Kasi\Support\LazyCollection<int, User>', $collection);

    return 'string';
}));

assertType("Kasi\Support\LazyCollection<int, array{string: string}>", $collection::make([['string' => 'string']])
    ->where('string'));
assertType("Kasi\Support\LazyCollection<int, array{string: string}>", $collection::make([['string' => 'string']])
    ->where('string', '=', 'string'));
assertType("Kasi\Support\LazyCollection<int, array{string: string}>", $collection::make([['string' => 'string']])
    ->where('string', 'string'));

assertType('Kasi\Support\LazyCollection<int, User>', $collection->whereNull());
assertType('Kasi\Support\LazyCollection<int, User>', $collection->whereNull('foo'));

assertType('Kasi\Support\LazyCollection<int, User>', $collection->whereNotNull());
assertType('Kasi\Support\LazyCollection<int, User>', $collection->whereNotNull('foo'));

assertType("Kasi\Support\LazyCollection<int, array{string: int}>", $collection::make([['string' => 2]])
    ->whereStrict('string', 2));

assertType("Kasi\Support\LazyCollection<int, array{string: int}>", $collection::make([['string' => 2]])
    ->whereIn('string', [2]));

assertType("Kasi\Support\LazyCollection<int, array{string: int}>", $collection::make([['string' => 2]])
    ->whereInStrict('string', [2]));

assertType("Kasi\Support\LazyCollection<int, array{string: int}>", $collection::make([['string' => 2]])
    ->whereBetween('string', [1, 3]));

assertType("Kasi\Support\LazyCollection<int, array{string: int}>", $collection::make([['string' => 2]])
    ->whereNotBetween('string', [1, 3]));

assertType("Kasi\Support\LazyCollection<int, array{string: int}>", $collection::make([['string' => 2]])
    ->whereNotIn('string', [2]));

assertType("Kasi\Support\LazyCollection<int, array{string: int}>", $collection::make([['string' => 2]])
    ->whereNotInStrict('string', [2]));

assertType('Kasi\Support\LazyCollection<int, User>', $collection::make([new User, 1])
    ->whereInstanceOf(User::class));

assertType('Kasi\Support\LazyCollection<int, User>', $collection::make([new User, 1])
    ->whereInstanceOf([User::class, User::class]));

assertType('User|null', $collection->first());
assertType('User|null', $collection->first(function ($user) {
    assertType('User', $user);

    return true;
}));
assertType("'string'|User", $collection->first(function ($user) {
    assertType('User', $user);

    return false;
}, 'string'));
assertType("'string'|User", $collection->first(null, function () {
    return 'string';
}));

assertType('User|null', $collection->last());
assertType('User|null', $collection->last(function ($user) {
    assertType('User', $user);

    return true;
}));
assertType("'string'|User", $collection->last(function ($user) {
    assertType('User', $user);

    return false;
}, 'string'));
assertType("'string'|User", $collection->last(null, function () {
    return 'string';
}));

assertType('Kasi\Support\LazyCollection<int, mixed>', $collection->flatten());
assertType('Kasi\Support\LazyCollection<int, mixed>', $collection::make(['string' => 'string'])->flatten(4));

assertType('User|null', $collection->firstWhere('string', 'string'));
assertType('User|null', $collection->firstWhere('string', 'string', 'string'));

assertType('Kasi\Support\LazyCollection<string, int>', $collection::make(['string'])->flip());

assertType('Kasi\Support\LazyCollection<(int|string), Kasi\Support\LazyCollection<int, User>>', $collection->groupBy('name'));
assertType('Kasi\Support\LazyCollection<(int|string), Kasi\Support\LazyCollection<int, User>>', $collection->groupBy('name', true));
assertType('Kasi\Support\LazyCollection<string, Kasi\Support\LazyCollection<int, User>>', $collection->groupBy(function ($user, $int) {
    assertType('User', $user);
    assertType('int', $int);

    return 'foo';
}));
assertType('Kasi\Support\LazyCollection<string, Kasi\Support\LazyCollection<string, User>>', $collection->keyBy(fn () => '')->groupBy(function ($user) {
    return 'foo';
}, true));

assertType('Kasi\Support\LazyCollection<(int|string), User>', $collection->keyBy('name'));
assertType('Kasi\Support\LazyCollection<string, User>', $collection->keyBy(function ($user, $int) {
    assertType('User', $user);
    assertType('int', $int);

    return 'foo';
}));

assertType('bool', $collection->has(0));
assertType('bool', $collection->has([0, 1]));

assertType('string', $collection->implode(function ($user, $index) {
    assertType('User', $user);
    assertType('int', $index);

    return 'string';
}));

assertType('Kasi\Support\LazyCollection<int, User>', $collection->intersect([new User]));

assertType('Kasi\Support\LazyCollection<int, User>', $collection->intersectByKeys([new User]));

assertType('Kasi\Support\LazyCollection<int, int>', $collection->keys());

assertType('User|null', $collection->last());
assertType('User|null', $collection->last(function ($user, $int) {
    assertType('User', $user);
    assertType('int', $int);

    return true;
}));
assertType("'string'|User", $collection->last(function () {
    return true;
}, 'string'));
assertType("'string'|User", $collection->last(null, function () {
    return 'string';
}));

assertType('Kasi\Support\LazyCollection<int, int>', $collection->map(function () {
    return 1;
}));
assertType('Kasi\Support\LazyCollection<int, string>', $collection->map(function () {
    return 'string';
}));

assertType('Kasi\Support\LazyCollection<int, string>', $collection::make(['string'])
    ->map(function ($string, $int) {
        assertType('string', $string);
        assertType('int', $int);

        return (string) $string;
    }));

assertType('Kasi\Support\LazyCollection<int, string>', $collection::make(['string'])
    ->mapSpread(function () {
        return 'string';
    }));

assertType('Kasi\Support\LazyCollection<int, int>', $collection::make(['string'])
    ->mapSpread(function () {
        return 1;
    }));

assertType('Kasi\Support\LazyCollection<string, array<int, int>>', $collection::make(['string', 'string'])
    ->mapToDictionary(function ($stringValue, $stringKey) {
        assertType('string', $stringValue);
        assertType('int', $stringKey);

        return ['string' => 1];
    }));

assertType('Kasi\Support\LazyCollection<string, Kasi\Support\LazyCollection<int, int>>', $collection::make(['string', 'string'])
    ->mapToGroups(function ($stringValue, $stringKey) {
        assertType('string', $stringValue);
        assertType('int', $stringKey);

        return ['string' => 1];
    }));

assertType('Kasi\Support\LazyCollection<string, int>', $collection::make(['string'])
    ->mapWithKeys(function ($string, $int) {
        assertType('string', $string);
        assertType('int', $int);

        return ['string' => 1];
    }));

assertType('Kasi\Support\LazyCollection<int, string>', $collection::make(['string'])
    ->flatMap(function ($string, $int) {
        assertType('string', $string);
        assertType('int', $int);

        return [0 => 'string'];
    }));

assertType('Kasi\Support\LazyCollection<int, User>', $collection->mapInto(User::class));

assertType('Kasi\Support\LazyCollection<int, int>', $collection->make([1])->merge([2]));
assertType('Kasi\Support\LazyCollection<int, string>', $collection->make(['string'])->merge(['string']));

assertType('Kasi\Support\LazyCollection<int, int>', $collection->make([1])->mergeRecursive([2]));
assertType('Kasi\Support\LazyCollection<int, string>', $collection->make(['string'])->mergeRecursive(['string']));

assertType('Kasi\Support\LazyCollection<string, int>', $collection->make(['string' => 'string'])->combine([2]));
assertType('Kasi\Support\LazyCollection<int, int>', $collection->make([1])->combine([1]));

assertType('Kasi\Support\LazyCollection<int, int>', $collection->make([1])->union([1]));
assertType('Kasi\Support\LazyCollection<string, string>', $collection->make(['string' => 'string'])->union(['string' => 'string']));

assertType('mixed', $collection->make()->min());
assertType('mixed', $collection->make([1])->min());
assertType('mixed', $collection->make([1])->min('string'));
assertType('mixed', $collection->make([1])->min(function ($int) {
    assertType('int', $int);

    return 1;
}));
assertType('mixed', $collection->make([new User])->min('id'));

assertType('mixed', $collection->make()->max());
assertType('mixed', $collection->make([1])->max());
assertType('mixed', $collection->make([1])->max('string'));
assertType('mixed', $collection->make([1])->max(function ($int) {
    assertType('int', $int);

    return 1;
}));
assertType('mixed', $collection->make([new User])->max('id'));

assertType('Kasi\Support\LazyCollection<int, User>', $collection->nth(1, 2));

assertType('Kasi\Support\LazyCollection<string, string>', $collection::make(['string' => 'string'])->only(['string']));
assertType('Kasi\Support\LazyCollection<int, User>', $collection->only([1]));
assertType('Kasi\Support\LazyCollection<int, string>', $collection::make(['string'])
    ->only([1]));

assertType('Kasi\Support\LazyCollection<int, User>', $collection->forPage(1, 2));

assertType('Kasi\Support\LazyCollection<int<0, 1>, Kasi\Support\LazyCollection<int, User>>', $collection->partition(function ($user, $int) {
    assertType('User', $user);
    assertType('int', $int);

    return true;
}));
assertType('Kasi\Support\LazyCollection<int<0, 1>, Kasi\Support\LazyCollection<int, string>>', $collection::make(['string'])->partition('string', '=', 'string'));
assertType('Kasi\Support\LazyCollection<int<0, 1>, Kasi\Support\LazyCollection<int, string>>', $collection::make(['string'])->partition('string', 'string'));
assertType('Kasi\Support\LazyCollection<int<0, 1>, Kasi\Support\LazyCollection<int, string>>', $collection::make(['string'])->partition('string'));

assertType('Kasi\Support\LazyCollection<int, int>', $collection->make([1])->concat([2]));
assertType('Kasi\Support\LazyCollection<int, string>', $collection->make(['string'])->concat(['string']));
assertType('Kasi\Support\LazyCollection<int, int|string>', $collection->make([1])->concat(['string']));

assertType('Kasi\Support\LazyCollection<int, int>|int', $collection->make([1])->random(2));
assertType('Kasi\Support\LazyCollection<int, string>|string', $collection->make(['string'])->random());

assertType('1', $collection
    ->reduce(function ($null, $user) {
        assertType('User', $user);
        assertType('1|null', $null);

        return 1;
    }));
assertType('1', $collection
    ->reduce(function ($int, $user) {
        assertType('User', $user);
        assertType('0|1', $int);

        return 1;
    }, 0));

assertType('Kasi\Support\LazyCollection<int, int>', $collection::make([1])->replace([1]));
assertType('Kasi\Support\LazyCollection<int, User>', $collection->replace([new User]));

assertType('Kasi\Support\LazyCollection<int, int>', $collection::make([1])->replaceRecursive([1]));
assertType('Kasi\Support\LazyCollection<int, User>', $collection->replaceRecursive([new User]));

assertType('Kasi\Support\LazyCollection<int, User>', $collection->reverse());

// assertType('int|bool', $collection->make([1])->search(2));
// assertType('string|bool', $collection->make(['string' => 'string'])->search('string'));
// assertType('int|bool', $collection->search(function ($user, $int) {
//     assertType('User', $user);
//     assertType('int', $int);

//    return true;
// }));

assertType('Kasi\Support\LazyCollection<int, int>', $collection->make([1])->shuffle());
assertType('Kasi\Support\LazyCollection<int, User>', $collection->shuffle());

assertType('Kasi\Support\LazyCollection<int, int>', $collection->make([1])->skip(1));
assertType('Kasi\Support\LazyCollection<int, User>', $collection->skip(1));

assertType('Kasi\Support\LazyCollection<int, int>', $collection->make([1])->skipUntil(1));
assertType('Kasi\Support\LazyCollection<int, User>', $collection->skipUntil(new User));
assertType('Kasi\Support\LazyCollection<int, User>', $collection->skipUntil(function ($user, $int) {
    assertType('User', $user);
    assertType('int', $int);

    return true;
}));

assertType('Kasi\Support\LazyCollection<int, int>', $collection->make([1])->skipWhile(1));
assertType('Kasi\Support\LazyCollection<int, User>', $collection->skipWhile(new User));
assertType('Kasi\Support\LazyCollection<int, User>', $collection->skipWhile(function ($user, $int) {
    assertType('User', $user);
    assertType('int', $int);

    return true;
}));

assertType('Kasi\Support\LazyCollection<int, int>', $collection->make([1])->slice(1));
assertType('Kasi\Support\LazyCollection<int, User>', $collection->slice(1, 2));

assertType('Kasi\Support\LazyCollection<int, Kasi\Support\LazyCollection<int, User>>', $collection->split(3));
assertType('Kasi\Support\LazyCollection<int, Kasi\Support\LazyCollection<int, int>>', $collection->make([1])->split(3));

assertType('string', $collection->make(['string' => 'string'])->sole('string', 'string'));
assertType('string', $collection->make(['string' => 'string'])->sole('string', '=', 'string'));
assertType('User', $collection->sole(function ($user, $int) {
    assertType('User', $user);
    assertType('int', $int);

    return true;
}));

assertType('User', $collection->firstOrFail());
assertType('User', $collection->firstOrFail('string', 'string'));
assertType('User', $collection->firstOrFail('string', '=', 'string'));
assertType('User', $collection->firstOrFail(function ($user, $int) {
    assertType('User', $user);
    assertType('int', $int);

    return true;
}));

assertType('Kasi\Support\LazyCollection<int, Kasi\Support\LazyCollection<int, string>>', $collection::make(['string'])->chunk(1));
assertType('Kasi\Support\LazyCollection<int, Kasi\Support\LazyCollection<int, User>>', $collection->chunk(2));

assertType('Kasi\Support\LazyCollection<int, Kasi\Support\LazyCollection<int, User>>', $collection->chunkWhile(function ($user, $int, $collection) {
    assertType('User', $user);
    assertType('int', $int);
    assertType('Kasi\Support\Collection<int, User>', $collection);

    return true;
}));

assertType('Kasi\Support\LazyCollection<int, User>', $collection->sort(function ($userA, $userB) {
    assertType('User', $userA);
    assertType('User', $userB);

    return 1;
}));
assertType('Kasi\Support\LazyCollection<int, User>', $collection->sort());

assertType('Kasi\Support\LazyCollection<int, User>', $collection->sortDesc());
assertType('Kasi\Support\LazyCollection<int, User>', $collection->sortDesc(2));

assertType('Kasi\Support\LazyCollection<int, User>', $collection->sortBy(function ($user, $int) {
    // assertType('User', $user);
    // assertType('int', $int);

    return 1;
}));
assertType('Kasi\Support\LazyCollection<int, User>', $collection->sortBy('string'));
assertType('Kasi\Support\LazyCollection<int, User>', $collection->sortBy('string', 1, false));
assertType('Kasi\Support\LazyCollection<int, User>', $collection->sortBy([
    ['string', 'string'],
]));
assertType('Kasi\Support\LazyCollection<int, User>', $collection->sortBy([function ($user, $int) {
    // assertType('User', $user);
    // assertType('int', $int);

    return 1;
}]));

assertType('Kasi\Support\LazyCollection<int, User>', $collection->sortByDesc(function ($user, $int) {
    // assertType('User', $user);
    // assertType('int', $int);

    return 1;
}));
assertType('Kasi\Support\LazyCollection<int, User>', $collection->sortByDesc('string'));
assertType('Kasi\Support\LazyCollection<int, User>', $collection->sortByDesc('string', 1));
assertType('Kasi\Support\LazyCollection<int, User>', $collection->sortByDesc([
    ['string', 'string'],
]));
assertType('Kasi\Support\LazyCollection<int, User>', $collection->sortByDesc([function ($user, $int) {
    // assertType('User', $user);
    // assertType('int', $int);

    return 1;
}]));

assertType('Kasi\Support\LazyCollection<int, int>', $collection->make([1])->sortKeys());
assertType('Kasi\Support\LazyCollection<string, string>', $collection->make(['string' => 'string'])->sortKeys(1, true));

assertType('Kasi\Support\LazyCollection<int, int>', $collection->make([1])->sortKeysDesc());
assertType('Kasi\Support\LazyCollection<string, string>', $collection->make(['string' => 'string'])->sortKeysDesc(1));

assertType('mixed', $collection->make([1])->sum('string'));
assertType('mixed', $collection->make(['string'])->sum(function ($string) {
    assertType('string', $string);

    return 1;
}));

assertType('Kasi\Support\LazyCollection<int, int>', $collection->make([1])->take(1));
assertType('Kasi\Support\LazyCollection<int, User>', $collection->take(1));

assertType('Kasi\Support\LazyCollection<int, int>', $collection->make([1])->takeUntil(1));
assertType('Kasi\Support\LazyCollection<int, User>', $collection->takeUntil(new User));
assertType('Kasi\Support\LazyCollection<int, User>', $collection->takeUntil(function ($user, $int) {
    assertType('User', $user);
    assertType('int', $int);

    return true;
}));

assertType('Kasi\Support\LazyCollection<int, User>', $collection->takeUntilTimeout(new DateTime()));

assertType('Kasi\Support\LazyCollection<int, int>', $collection->make([1])->takeWhile(1));
assertType('Kasi\Support\LazyCollection<int, User>', $collection->takeWhile(new User));
assertType('Kasi\Support\LazyCollection<int, User>', $collection->takeWhile(function ($user, $int) {
    assertType('User', $user);
    assertType('int', $int);

    return true;
}));

assertType('Kasi\Support\LazyCollection<int, User>', $collection->tap(function ($collection) {
    assertType('Kasi\Support\LazyCollection<int, User>', $collection);
}));

assertType('Kasi\Support\LazyCollection<int, 1>', $collection->pipe(function ($collection) {
    assertType('Kasi\Support\LazyCollection<int, User>', $collection);

    return new LazyCollection([1]);
}));
assertType('1', $collection->make([1])->pipe(function ($collection) {
    assertType('Kasi\Support\LazyCollection<int, int>', $collection);

    return 1;
}));

assertType('User', $collection->pipeInto(User::class));

assertType('Kasi\Support\LazyCollection<(int|string), mixed>', $collection->make(['string' => 'string'])->pluck('string'));
assertType('Kasi\Support\LazyCollection<(int|string), mixed>', $collection->make(['string' => 'string'])->pluck('string', 'string'));

assertType('Kasi\Support\LazyCollection<int, User>', $collection->reject());
assertType('Kasi\Support\LazyCollection<int, User>', $collection->reject(function ($user) {
    assertType('User', $user);

    return true;
}));

assertType('Kasi\Support\LazyCollection<int, User>', $collection->tapEach(function ($user, $int) {
    assertType('User', $user);
    assertType('int', $int);

    return null;
}));

assertType('Kasi\Support\LazyCollection<int, User>', $collection->unique());
assertType('Kasi\Support\LazyCollection<int, User>', $collection->unique(function ($user, $int) {
    assertType('User', $user);
    assertType('int', $int);

    return $user->getTable();
}));
assertType('Kasi\Support\LazyCollection<string, string>', $collection->make(['string' => 'string'])->unique(function ($stringA, $stringB) {
    assertType('string', $stringA);
    assertType('string', $stringB);

    return $stringA;
}, true));

assertType('Kasi\Support\LazyCollection<int, User>', $collection->uniqueStrict());
assertType('Kasi\Support\LazyCollection<int, User>', $collection->uniqueStrict(function ($user, $int) {
    assertType('User', $user);
    assertType('int', $int);

    return $user->getTable();
}));

assertType('Kasi\Support\LazyCollection<int, User>', $collection->values());
assertType('Kasi\Support\LazyCollection<int, string>', $collection::make(['string', 'string'])->values());
assertType('Kasi\Support\LazyCollection<int, int|string>', $collection::make(['string', 1])->values());

assertType('Kasi\Support\LazyCollection<int, int>', $collection->make([1])->pad(2, 0));
assertType('Kasi\Support\LazyCollection<int, int|string>', $collection->make([1])->pad(2, 'string'));
assertType('Kasi\Support\LazyCollection<int, int|User>', $collection->pad(2, 0));

assertType('Kasi\Support\LazyCollection<(int|string), int>', $collection->make([1])->countBy());
assertType('Kasi\Support\LazyCollection<(int|string), int>', $collection->make(['string' => 'string'])->countBy('string'));
assertType('Kasi\Support\LazyCollection<(int|string), int>', $collection->make(['string'])->countBy(function ($string, $int) {
    assertType('string', $string);
    assertType('int', $int);

    return $string;
}));

assertType('Kasi\Support\LazyCollection<int, Kasi\Support\LazyCollection<int, int|User>>', $collection->zip([1]));
assertType('Kasi\Support\LazyCollection<int, Kasi\Support\LazyCollection<int, string|User>>', $collection->zip(['string']));
assertType('Kasi\Support\LazyCollection<int, Kasi\Support\LazyCollection<int, string>>', $collection::make(['string' => 'string'])->zip(['string']));

assertType('Kasi\Support\Collection<int, User>', $collection->collect());
assertType('Kasi\Support\Collection<int, int>', $collection->make([1])->collect());

assertType('array<int, User>', $collection->all());

assertType('User|null', $collection->get(0));
assertType("'string'|User", $collection->get(0, 'string'));
assertType("'string'|User", $collection->get(0, function () {
    return 'string';
}));

assertType(
    'Kasi\Support\LazyCollection<int, Kasi\Support\LazyCollection<int, User>>',
    $collection->sliding(2)
);

assertType(
    'Kasi\Support\LazyCollection<int, Kasi\Support\LazyCollection<string, string>>',
    $collection::make(['string' => 'string'])->sliding(2, 1)
);

assertType(
    'Kasi\Support\LazyCollection<int, Kasi\Support\LazyCollection<int, User>>',
    $collection->splitIn(2)
);

assertType(
    'Kasi\Support\LazyCollection<int, Kasi\Support\LazyCollection<string, string>>',
    $collection::make(['string' => 'string'])->splitIn(1)
);

/**
 * @template TKey of array-key
 * @template TValue
 *
 * @extends \Kasi\Support\LazyCollection<TKey, TValue>
 */
class CustomLazyCollection extends LazyCollection
{
}

// assertType('CustomLazyCollection<int, User>', CustomLazyCollection::make([new User]));

assertType('array<int, mixed>', $collection->toArray());
assertType('array<string, mixed>', LazyCollection::make(['string' => 'string'])->toArray());
assertType('array<int, mixed>', LazyCollection::make([1, 2])->toArray());

assertType('Traversable<int, User>', $collection->getIterator());
foreach ($collection as $int => $user) {
    assertType('int', $int);
    assertType('User', $user);
}

class LazyAnimal
{
}
class LazyTiger extends LazyAnimal
{
}
class LazyLion extends LazyAnimal
{
}
class LazyZebra extends LazyAnimal
{
}

class LazyZoo
{
    /**
     * @var \Kasi\Support\Collection<int, LazyAnimal>
     */
    private Collection $animals;

    public function __construct()
    {
        $this->animals = collect([
            new LazyTiger,
            new LazyLion,
            new LazyZebra,
        ]);
    }

    /**
     * @return \Kasi\Support\LazyCollection<int, LazyAnimal>
     */
    public function getWithoutZebras(): LazyCollection
    {
        return $this->animals->lazy()->filter(fn (LazyAnimal $animal) => ! $animal instanceof LazyZebra);
    }
}

$zoo = new LazyZoo();

$coll = $zoo->getWithoutZebras();

assertType('Kasi\Support\LazyCollection<int, LazyAnimal>', $coll);
assertType('Kasi\Support\HigherOrderCollectionProxy<int, LazyAnimal>', $coll->average);
assertType('Kasi\Support\HigherOrderCollectionProxy<int, LazyAnimal>', $coll->avg);
assertType('Kasi\Support\HigherOrderCollectionProxy<int, LazyAnimal>', $coll->contains);
assertType('Kasi\Support\HigherOrderCollectionProxy<int, LazyAnimal>', $coll->doesntContain);
assertType('Kasi\Support\HigherOrderCollectionProxy<int, LazyAnimal>', $coll->each);
assertType('Kasi\Support\HigherOrderCollectionProxy<int, LazyAnimal>', $coll->every);
assertType('Kasi\Support\HigherOrderCollectionProxy<int, LazyAnimal>', $coll->filter);
assertType('Kasi\Support\HigherOrderCollectionProxy<int, LazyAnimal>', $coll->first);
assertType('Kasi\Support\HigherOrderCollectionProxy<int, LazyAnimal>', $coll->flatMap);
assertType('Kasi\Support\HigherOrderCollectionProxy<int, LazyAnimal>', $coll->groupBy);
assertType('Kasi\Support\HigherOrderCollectionProxy<int, LazyAnimal>', $coll->keyBy);
assertType('Kasi\Support\HigherOrderCollectionProxy<int, LazyAnimal>', $coll->last);
assertType('Kasi\Support\HigherOrderCollectionProxy<int, LazyAnimal>', $coll->map);
assertType('Kasi\Support\HigherOrderCollectionProxy<int, LazyAnimal>', $coll->max);
assertType('Kasi\Support\HigherOrderCollectionProxy<int, LazyAnimal>', $coll->min);
assertType('Kasi\Support\HigherOrderCollectionProxy<int, LazyAnimal>', $coll->partition);
assertType('Kasi\Support\HigherOrderCollectionProxy<int, LazyAnimal>', $coll->percentage);
assertType('Kasi\Support\HigherOrderCollectionProxy<int, LazyAnimal>', $coll->reject);
assertType('Kasi\Support\HigherOrderCollectionProxy<int, LazyAnimal>', $coll->skipUntil);
assertType('Kasi\Support\HigherOrderCollectionProxy<int, LazyAnimal>', $coll->skipWhile);
assertType('Kasi\Support\HigherOrderCollectionProxy<int, LazyAnimal>', $coll->some);
assertType('Kasi\Support\HigherOrderCollectionProxy<int, LazyAnimal>', $coll->sortBy);
assertType('Kasi\Support\HigherOrderCollectionProxy<int, LazyAnimal>', $coll->sortByDesc);
assertType('Kasi\Support\HigherOrderCollectionProxy<int, LazyAnimal>', $coll->sum);
assertType('Kasi\Support\HigherOrderCollectionProxy<int, LazyAnimal>', $coll->takeUntil);
assertType('Kasi\Support\HigherOrderCollectionProxy<int, LazyAnimal>', $coll->takeWhile);
assertType('Kasi\Support\HigherOrderCollectionProxy<int, LazyAnimal>', $coll->unique);
assertType('Kasi\Support\HigherOrderCollectionProxy<int, LazyAnimal>', $coll->unless);
assertType('Kasi\Support\HigherOrderCollectionProxy<int, LazyAnimal>', $coll->until);
assertType('Kasi\Support\HigherOrderCollectionProxy<int, LazyAnimal>', $coll->when);
