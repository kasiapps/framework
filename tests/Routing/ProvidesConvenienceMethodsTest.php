<?php

declare(strict_types=1);

use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Contracts\Validation\Factory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Validator;
use Laravel\Lumen\Routing\ProvidesConvenienceMethods;
use Mockery as m;

beforeEach(function () {
  $this->mock = new class
  {
    use ProvidesConvenienceMethods;
  };
});

afterEach(function () {
  m::close();
});

it('sets response builder callback', function () {
  $callback = fn ($request, $errors) => new JsonResponse(['custom' => $errors], 400);

  $this->mock::buildResponseUsing($callback);

  // Access the static property via reflection
  $reflection = new ReflectionClass($this->mock);
  $property = $reflection->getProperty('responseBuilder');
  $property->setAccessible(true);

  expect($property->getValue())->toBe($callback);
});

it('sets error formatter callback', function () {
  $callback = fn ($validator) => ['formatted' => $validator->errors()];

  $this->mock::formatErrorsUsing($callback);

  // Access the static property via reflection
  $reflection = new ReflectionClass($this->mock);
  $property = $reflection->getProperty('errorFormatter');
  $property->setAccessible(true);

  expect($property->getValue())->toBe($callback);
});

it('validates request successfully', function () {
  $request = m::mock(Request::class);
  $request->shouldReceive('all')->andReturn(['name' => 'John', 'email' => 'john@example.com']);
  $request->shouldReceive('only')->with(['name', 'email'])->andReturn(['name' => 'John', 'email' => 'john@example.com']);

  $validator = m::mock(Validator::class);
  $validator->shouldReceive('fails')->andReturn(false);

  $factory = m::mock(Factory::class);
  $factory->shouldReceive('make')->with(['name' => 'John', 'email' => 'john@example.com'], ['name' => 'required', 'email' => 'required|email'], [], [])->andReturn($validator);

  app()->instance('validator', $factory);

  $result = $this->mock->validate($request, ['name' => 'required', 'email' => 'required|email']);

  expect($result)->toBe(['name' => 'John', 'email' => 'john@example.com']);
});

it('authorizes ability', function () {
  $gate = m::mock(Gate::class);
  $gate->shouldReceive('authorize')->with('edit-post', [])->andReturn(true);

  app()->instance(Gate::class, $gate);

  $result = $this->mock->authorize('edit-post');

  expect($result)->toBeTrue();
});

it('authorizes ability for user', function () {
  $user = m::mock();
  $gate = m::mock(Gate::class);
  $gate->shouldReceive('forUser')->with($user)->andReturnSelf();
  $gate->shouldReceive('authorize')->with('edit-post', [])->andReturn(true);

  app()->instance(Gate::class, $gate);

  $result = $this->mock->authorizeForUser($user, 'edit-post');

  expect($result)->toBeTrue();
});

it('dispatches job', function () {
  $job = new stdClass();
  $dispatcher = m::mock(Dispatcher::class);
  $dispatcher->shouldReceive('dispatch')->with($job)->andReturn('dispatched');

  app()->instance(Dispatcher::class, $dispatcher);

  $result = $this->mock->dispatch($job);

  expect($result)->toBe('dispatched');
});

it('dispatches job now', function () {
  $job = new stdClass();
  $dispatcher = m::mock(Dispatcher::class);
  $dispatcher->shouldReceive('dispatchNow')->with($job, null)->andReturn('dispatched-now');

  app()->instance(Dispatcher::class, $dispatcher);

  $result = $this->mock->dispatchNow($job);

  expect($result)->toBe('dispatched-now');
});
