<?php

use Kasi\Config\Repository;

use function PHPStan\Testing\assertType;

assertType('Kasi\Foundation\Application', app());
assertType('mixed', app('foo'));
assertType('Kasi\Config\Repository', app(Repository::class));

assertType('Kasi\Contracts\Auth\Factory', auth());
assertType('Kasi\Contracts\Auth\StatefulGuard', auth('foo'));

assertType('Kasi\Cache\CacheManager', cache());
assertType('bool', cache(['foo' => 'bar'], 42));
assertType('mixed', cache('foo', 42));

assertType('Kasi\Config\Repository', config());
assertType('null', config(['foo' => 'bar']));
assertType('mixed', config('foo'));

assertType('Kasi\Log\Context\Repository', context());
assertType('Kasi\Log\Context\Repository', context(['foo' => 'bar']));
assertType('mixed', context('foo'));

assertType('Kasi\Cookie\CookieJar', cookie());
assertType('Symfony\Component\HttpFoundation\Cookie', cookie('foo'));

assertType('Kasi\Foundation\Bus\PendingDispatch', dispatch('foo'));
assertType('Kasi\Foundation\Bus\PendingClosureDispatch', dispatch(fn () => 1));

assertType('Kasi\Log\LogManager', logger());
assertType('null', logger('foo'));

assertType('Kasi\Log\LogManager', logs());
assertType('Psr\Log\LoggerInterface', logs('foo'));

assertType('123|null', rescue(fn () => 123));
assertType('123|345', rescue(fn () => 123, 345));
assertType('123|345', rescue(fn () => 123, fn () => 345));

assertType('Kasi\Routing\Redirector', redirect());
assertType('Kasi\Http\RedirectResponse', redirect('foo'));

assertType('mixed', resolve('foo'));
assertType('Kasi\Config\Repository', resolve(Repository::class));

assertType('Kasi\Http\Request', request());
assertType('mixed', request('foo'));
assertType('array<string, mixed>', request(['foo', 'bar']));

assertType('Kasi\Contracts\Routing\ResponseFactory', response());
assertType('Kasi\Http\Response', response('foo'));

assertType('Kasi\Session\SessionManager', session());
assertType('mixed', session('foo'));
assertType('null', session(['foo' => 'bar']));

assertType('Kasi\Contracts\Translation\Translator', trans());
assertType('array|string', trans('foo'));

assertType('Kasi\Contracts\Validation\Factory', validator());
assertType('Kasi\Contracts\Validation\Validator', validator([]));

assertType('Kasi\Contracts\View\Factory', view());
assertType('Kasi\Contracts\View\View', view('foo'));

assertType('Kasi\Contracts\Routing\UrlGenerator', url());
assertType('string', url('foo'));
