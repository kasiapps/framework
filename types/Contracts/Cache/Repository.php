<?php

use Kasi\Contracts\Cache\Repository;

use function PHPStan\Testing\assertType;

/** @var Repository $cache */
$cache = resolve(Repository::class);

assertType('mixed', $cache->get('key'));
assertType('mixed', $cache->get('cache', 27));
assertType('mixed', $cache->get('cache', function (): int {
    return 26;
}));

assertType('mixed', $cache->pull('key'));
assertType('28', $cache->pull('cache', 28));
assertType('30', $cache->pull('cache', function (): int {
    return 30;
}));
assertType('33', $cache->sear('cache', function (): int {
    return 33;
}));
assertType('36', $cache->remember('cache', now(), function (): int {
    return 36;
}));
assertType('36', $cache->rememberForever('cache', function (): int {
    return 36;
}));
