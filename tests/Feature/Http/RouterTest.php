<?php

declare(strict_types=1);

use Laravel\Lumen\Application;
use Laravel\Lumen\Routing\Router;

afterEach(function () {
    // Restore error handlers to prevent warnings
    restore_error_handler();
    restore_exception_handler();
});

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

it('registers HEAD routes', function () {
  $router = new Router(createApp());

  $router->head('/api/status', 'StatusController@head');

  $routes = $router->getRoutes();
  expect($routes)->toHaveKey('HEAD/api/status');
  expect($routes['HEAD/api/status']['method'])->toBe('HEAD');
});

it('registers PUT routes', function () {
  $router = new Router(createApp());

  $router->put('/users/{id}', 'UserController@update');

  $routes = $router->getRoutes();
  expect($routes)->toHaveKey('PUT/users/{id}');
  expect($routes['PUT/users/{id}']['method'])->toBe('PUT');
});

it('registers PATCH routes', function () {
  $router = new Router(createApp());

  $router->patch('/users/{id}', 'UserController@patch');

  $routes = $router->getRoutes();
  expect($routes)->toHaveKey('PATCH/users/{id}');
  expect($routes['PATCH/users/{id}']['method'])->toBe('PATCH');
});

it('merges group attributes with domain handling', function () {
  $router = new Router(createApp());

  // Test the domain unset logic (line 89)
  $new = ['domain' => 'api.example.com', 'prefix' => 'v1'];
  $old = ['domain' => 'old.example.com', 'middleware' => ['auth']];

  $merged = $router->mergeGroup($new, $old);

  expect($merged['domain'])->toBe('api.example.com');
  expect($merged)->not->toHaveKey('old'); // old domain should be unset
  expect($merged['middleware'])->toBe(['auth']);
});
