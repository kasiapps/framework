<?php

/**
 * TEST CASE
 *
 * The closure you provide to your test functions is always bound to a specific PHPUnit test
 * case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
 * need to change it using the "pest()" function to bind a different classes or traits.
 */
pest()->extend(Tests\TestCase::class)
  ->in('Feature', 'Unit');

/**
 * GLOBAL SETUP & CLEANUP
 *
 * Global setup and cleanup functions that run for all tests to ensure
 * consistent test environment and prevent test pollution.
 */

// Global cleanup after each test
afterEach(function () {
  // Close Mockery to prevent memory leaks
  if (class_exists('Mockery')) {
    Mockery::close();
  }

  // Restore error handlers to prevent warnings
  restore_error_handler();
  restore_exception_handler();

  // Clear any global state that might affect other tests
  if (isset($_SERVER['__middleware.response'])) {
    unset($_SERVER['__middleware.response']);
  }
});

/**
 * EXPECTATIONS
 *
 * When you're writing tests, you often need to check that values meet certain conditions. The
 * "expect()" function gives you access to a set of "expectations" methods that you can use
 * to assert different things. Of course, you may extend the Expectation API at any time.
 */
expect()->extend('toBeOne', function () {
  return $this->toBe(1);
});

/**
 * FUNCTIONS
 *
 * While Pest is very powerful out-of-the-box, you may have some testing code specific to your
 * project that you don't want to repeat in every file. Here you can also expose helpers as
 * global functions to help you to reduce the number of lines of code in your test files.
 */

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\JsonResponse;
use Laravel\Lumen\Application;
use Laravel\Lumen\Http\Request;
use Mockery as m;

/**
 * CREATE A BASIC APPLICATION INSTANCE FOR TESTING.
 */
function createApp(): Application
{
  return new Application();
}

/**
 * CREATE A MOCK REQUEST WITH COMMON SETUP.
 */
function mockRequest(array $data = [], string $method = 'GET', string $uri = '/'): Request
{
  $request = m::mock(Request::class);
  $request->shouldReceive('all')->andReturn($data);
  $request->shouldReceive('only')->andReturn($data);
  $request->shouldReceive('method')->andReturn($method);
  $request->shouldReceive('getUri')->andReturn($uri);

  return $request;
}

/**
 * CREATE A MOCK VALIDATOR WITH COMMON SETUP.
 */
function mockValidator(bool $fails = false, array $errors = []): Validator
{
  $validator = m::mock(Validator::class);
  $validator->shouldReceive('fails')->andReturn($fails);

  if ($fails) {
    $errorBag = m::mock();
    $errorBag->shouldReceive('getMessages')->andReturn($errors);
    $validator->shouldReceive('errors')->andReturn($errorBag);
  }

  return $validator;
}

/**
 * CREATE A MOCK USER FOR AUTHENTICATION TESTING.
 */
function mockUser(array $attributes = []): Authenticatable
{
  $user = m::mock(Authenticatable::class);

  foreach ($attributes as $key => $value) {
    $user->shouldReceive('getAttribute')->with($key)->andReturn($value);
    $user->shouldReceive($key)->andReturn($value);
    // Also handle property access
    $user->{$key} = $value;
  }

  return $user;
}

/**
 * ACCESS PROTECTED/PRIVATE METHODS VIA REFLECTION.
 */
function callProtectedMethod(object $object, string $method, array $args = [])
{
  $reflection = new ReflectionClass($object);
  $method = $reflection->getMethod($method);
  $method->setAccessible(true);

  return $method->invokeArgs($object, $args);
}

/**
 * ACCESS PROTECTED/PRIVATE PROPERTIES VIA REFLECTION.
 */
function getProtectedProperty(object $object, string $property)
{
  $reflection = new ReflectionClass($object);
  $property = $reflection->getProperty($property);
  $property->setAccessible(true);

  return $property->getValue($object);
}

/**
 * SET PROTECTED/PRIVATE PROPERTIES VIA REFLECTION.
 */
function setProtectedProperty(object $object, string $property, $value): void
{
  $reflection = new ReflectionClass($object);
  $property = $reflection->getProperty($property);
  $property->setAccessible(true);
  $property->setValue($object, $value);
}

/**
 * CREATE A JSON RESPONSE FOR TESTING.
 */
function jsonResponse(array $data = [], int $status = 200): JsonResponse
{
  return new JsonResponse($data, $status);
}

/**
 * ASSERT THAT A CLASS HAS SPECIFIC METHODS.
 */
function assertClassHasMethods(string $class, array $methods): void
{
  $reflection = new ReflectionClass($class);

  foreach ($methods as $method) {
    expect($reflection->hasMethod($method))->toBeTrue("Class {$class} should have method {$method}");
  }
}

/**
 * ASSERT THAT A CLASS HAS SPECIFIC PROPERTIES.
 */
function assertClassHasProperties(string $class, array $properties): void
{
  $reflection = new ReflectionClass($class);

  foreach ($properties as $property) {
    expect($reflection->hasProperty($property))->toBeTrue("Class {$class} should have property {$property}");
  }
}

/**
 * BIND A MOCK SERVICE TO THE APPLICATION CONTAINER.
 */
function bindMockService(Application $app, string $abstract, $mock = null): void
{
  if ($mock === null) {
    $mock = m::mock($abstract);
  }

  $app->instance($abstract, $mock);
}

/**
 * SETUP COMMON MOCKS FOR VALIDATION TESTING.
 */
function setupValidationMocks(Application $app, bool $fails = false, array $errors = []): void
{
  $validator = mockValidator($fails, $errors);
  $factory = m::mock(\Illuminate\Contracts\Validation\Factory::class);
  $factory->shouldReceive('make')->andReturn($validator);

  bindMockService($app, 'validator', $factory);
}

/**
 * SETUP COMMON MOCKS FOR AUTHENTICATION TESTING.
 */
function setupAuthMocks(Application $app, ?Authenticatable $user = null): void
{
  $guard = m::mock();

  if ($user) {
    $guard->shouldReceive('setUser')->with($user);
    $guard->shouldReceive('user')->andReturn($user);
  }

  $auth = m::mock();
  $auth->shouldReceive('guard')->andReturn($guard);

  bindMockService($app, 'auth', $auth);
}

/**
 * SETUP COMMON MOCKS FOR GATE/AUTHORIZATION TESTING.
 */
function setupGateMocks(Application $app, bool $authorized = true): void
{
  $gate = m::mock(\Illuminate\Contracts\Auth\Access\Gate::class);
  $gate->shouldReceive('authorize')->andReturn($authorized);
  $gate->shouldReceive('forUser')->andReturnSelf();

  bindMockService($app, \Illuminate\Contracts\Auth\Access\Gate::class, $gate);
}

/**
 * SETUP COMMON MOCKS FOR JOB DISPATCHING TESTING.
 */
function setupDispatcherMocks(Application $app): void
{
  $dispatcher = m::mock(\Illuminate\Bus\Dispatcher::class);
  $dispatcher->shouldReceive('dispatch')->andReturn('dispatched');
  $dispatcher->shouldReceive('dispatchNow')->andReturn('dispatched-now');

  bindMockService($app, \Illuminate\Bus\Dispatcher::class, $dispatcher);
}
