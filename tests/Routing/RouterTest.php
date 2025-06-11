<?php

declare(strict_types=1);

use Laravel\Lumen\Application;
use Laravel\Lumen\Routing\Router;

it('has router instance', function () {
  $router = new Router(new Application());

  expect($router)->toBeInstanceOf(Router::class);
});

it('registers DELETE routes', function () {
  $router = new Router(new Application());

  $router->delete('/users/{id}', 'UserController@destroy');

  $routes = $router->getRoutes();
  expect($routes)->toHaveKey('DELETE/users/{id}');
  expect($routes['DELETE/users/{id}']['method'])->toBe('DELETE');
});

it('registers OPTIONS routes', function () {
  $router = new Router(new Application());

  $router->options('/api/info', 'ApiController@options');

  $routes = $router->getRoutes();
  expect($routes)->toHaveKey('OPTIONS/api/info');
  expect($routes['OPTIONS/api/info']['method'])->toBe('OPTIONS');
});

it('merges group attributes correctly', function () {
  $router = new Router(new Application());

  $new = ['prefix' => 'api', 'middleware' => ['auth']];
  $old = ['prefix' => 'v1', 'as' => 'api.', 'middleware' => ['throttle']];

  $merged = $router->mergeGroup($new, $old);

  expect($merged['prefix'])->toBe('v1/api');
  expect($merged['as'])->toBe('api.');
  expect($merged['middleware'])->toBe(['throttle', 'auth']);
});

it('checks if router has group stack', function () {
  $router = new Router(new Application());

  expect($router->hasGroupStack())->toBeFalse();

  $router->group(['prefix' => 'api'], function ($router) {
    expect($router->hasGroupStack())->toBeTrue();
  });

  expect($router->hasGroupStack())->toBeFalse();
});
