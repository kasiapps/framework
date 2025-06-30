<?php

use Kasi\Config\Repository;
use Kasi\Contracts\Container\Container;

use function PHPStan\Testing\assertType;

$container = resolve(Container::class);

assertType('stdClass', $container->instance('foo', new stdClass));

assertType('mixed', $container->get('foo'));
assertType('Kasi\Config\Repository', $container->get(Repository::class));

assertType('Closure(): mixed', $container->factory('foo'));
assertType('Closure(): Kasi\Config\Repository', $container->factory(Repository::class));

assertType('mixed', $container->make('foo'));
assertType('Kasi\Config\Repository', $container->make(Repository::class));
