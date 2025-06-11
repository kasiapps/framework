<?php

declare(strict_types=1);

use Laravel\Lumen\Http\Request;
use Mockery as m;

beforeEach(function () {
    $this->request = new Request();
});

afterEach(function () {
    m::close();
});

it('checks route name with routeIs method', function () {
    $routeResolver = function () {
        return [
            null,
            ['as' => 'users.index'],
            []
        ];
    };
    
    $this->request->setRouteResolver($routeResolver);
    
    expect($this->request->routeIs('users.index'))->toBeTrue();
    expect($this->request->routeIs('users.*'))->toBeTrue();
    expect($this->request->routeIs('posts.index'))->toBeFalse();
});

it('returns false when route has no name', function () {
    $routeResolver = function () {
        return [
            null,
            [], // No 'as' key
            []
        ];
    };
    
    $this->request->setRouteResolver($routeResolver);
    
    expect($this->request->routeIs('users.index'))->toBeFalse();
});

it('gets route information', function () {
    $routeResolver = function () {
        return [
            null,
            ['as' => 'users.show'],
            ['id' => 123, 'slug' => 'test-slug']
        ];
    };
    
    $this->request->setRouteResolver($routeResolver);
    
    $route = $this->request->route();
    expect($route)->toBe([
        null,
        ['as' => 'users.show'],
        ['id' => 123, 'slug' => 'test-slug']
    ]);
    
    expect($this->request->route('id'))->toBe(123);
    expect($this->request->route('slug'))->toBe('test-slug');
    expect($this->request->route('missing', 'default'))->toBe('default');
});

it('returns null when no route resolver', function () {
    expect($this->request->route())->toBeNull();
});

it('generates fingerprint', function () {
    $routeResolver = function () {
        return [
            null,
            ['as' => 'users.index'],
            []
        ];
    };
    
    $this->request->setRouteResolver($routeResolver);
    $this->request->server->set('REQUEST_METHOD', 'GET');
    $this->request->server->set('HTTP_HOST', 'example.com');
    $this->request->server->set('REQUEST_URI', '/users');
    $this->request->server->set('REMOTE_ADDR', '127.0.0.1');
    
    $fingerprint = $this->request->fingerprint();
    
    expect($fingerprint)->toBeString();
    expect(strlen($fingerprint))->toBe(40); // SHA1 hash length
});

it('throws exception when generating fingerprint without route', function () {
    expect(fn () => $this->request->fingerprint())
        ->toThrow(RuntimeException::class, 'Unable to generate fingerprint. Route unavailable.');
});

it('checks offset exists', function () {
    $routeResolver = function () {
        return [
            null,
            [],
            ['id' => 123]
        ];
    };
    
    $this->request->setRouteResolver($routeResolver);
    $this->request->merge(['name' => 'John']);
    
    expect($this->request->offsetExists('name'))->toBeTrue();
    expect($this->request->offsetExists('id'))->toBeTrue();
    expect($this->request->offsetExists('missing'))->toBeFalse();
});
