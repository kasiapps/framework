<?php

declare(strict_types=1);

use Illuminate\Config\Repository as Config;
use Illuminate\Container\Container;
use Illuminate\Log\LogManager;
use Laravel\Lumen\Concerns\RegistersExceptionHandlers;
use Mockery as m;
use Monolog\Handler\NullHandler;

beforeEach(function () {
  $this->container = new Container;
  $this->config = new Config;
  $this->container->singleton('config', fn (): Config => $this->config);

  // Set error reporting to include deprecations
  error_reporting(E_ALL);
});

afterEach(function () {
  $this->container::setInstance(null);
  m::close();
});

// Helper function to access the trait methods
function getTestInstance($container, $config)
{
  return new class($container, $config)
  {
    use RegistersExceptionHandlers;

    public $container;

    public $config;

    public function __construct($container, $config)
    {
      $this->container = $container;
      $this->config = $config;
    }

    protected function make($abstract, array $parameters = [])
    {
      return $this->container->make($abstract, $parameters);
    }

    protected function bound($abstract)
    {
      return $this->container->bound($abstract);
    }

    protected function runningInConsole()
    {
      return false; // For testing purposes
    }
  };
}

it('handles PHP deprecations', function () {
  $testInstance = getTestInstance($this->container, $this->config);

  $mock = m::mock(LogManager::class);
  $this->container->instance('log', $mock);
  $mock->shouldReceive('channel')->with('deprecations')->andReturnSelf();
  $mock->shouldReceive('warning')->with(sprintf('%s in %s on line %s',
    'str_contains(): Passing null to parameter #2 ($needle) of type string is deprecated',
    '/home/user/laravel/routes/web.php',
    17
  ));

  $testInstance->handleError(
    E_DEPRECATED,
    'str_contains(): Passing null to parameter #2 ($needle) of type string is deprecated',
    '/home/user/laravel/routes/web.php',
    17
  );
});

it('handles user deprecations', function () {
  $testInstance = getTestInstance($this->container, $this->config);

  $mock = m::mock(LogManager::class);
  $this->container->instance('log', $mock);
  $mock->shouldReceive('channel')->with('deprecations')->andReturnSelf();
  $mock->shouldReceive('warning')->with(sprintf('%s in %s on line %s',
    'str_contains(): Passing null to parameter #2 ($needle) of type string is deprecated',
    '/home/user/laravel/routes/web.php',
    17
  ));

  $testInstance->handleError(
    E_USER_DEPRECATED,
    'str_contains(): Passing null to parameter #2 ($needle) of type string is deprecated',
    '/home/user/laravel/routes/web.php',
    17
  );
});

it('handles errors', function () {
  $testInstance = getTestInstance($this->container, $this->config);

  $mock = m::mock(LogManager::class);
  $this->container->instance('log', $mock);
  $mock->shouldNotReceive('channel');
  $mock->shouldNotReceive('warning');

  expect(fn () => $testInstance->handleError(
    E_ERROR,
    'Something went wrong',
    '/home/user/laravel/src/Providers/AppServiceProvider.php',
    17
  ))->toThrow(ErrorException::class, 'Something went wrong');
});

it('ensures deprecations driver', function () {
  $testInstance = getTestInstance($this->container, $this->config);

  $mock = m::mock(LogManager::class);
  $this->container->instance('log', $mock);
  $mock->shouldReceive('channel')->andReturnSelf();
  $mock->shouldReceive('warning');

  $this->config->set('logging.channels.stack', [
    'driver' => 'stack',
    'channels' => ['single'],
    'ignore_exceptions' => false,
  ]);
  $this->config->set('logging.deprecations', 'stack');

  $testInstance->handleError(
    E_USER_DEPRECATED,
    'str_contains(): Passing null to parameter #2 ($needle) of type string is deprecated',
    '/home/user/laravel/routes/web.php',
    17
  );

  expect($this->config->get('logging.channels.deprecations'))->toBe([
    'driver' => 'stack',
    'channels' => ['single'],
    'ignore_exceptions' => false,
  ]);
});

it('ensures null deprecations driver', function () {
  $testInstance = getTestInstance($this->container, $this->config);

  $mock = m::mock(LogManager::class);
  $this->container->instance('log', $mock);
  $mock->shouldReceive('channel')->andReturnSelf();
  $mock->shouldReceive('warning');

  $this->config->set('logging.channels.null', [
    'driver' => 'monolog',
    'handler' => NullHandler::class,
  ]);

  $testInstance->handleError(
    E_USER_DEPRECATED,
    'str_contains(): Passing null to parameter #2 ($needle) of type string is deprecated',
    '/home/user/laravel/routes/web.php',
    17
  );

  expect($this->config->get('logging.channels.deprecations.handler'))->toBe(NullHandler::class);
});

it('has no deprecations driver if no deprecations here send', function () {
  expect($this->config->get('logging.deprecations'))->toBeNull();
  expect($this->config->get('logging.channels.deprecations'))->toBeNull();
});

it('ignores deprecation if logger unresolvable', function () {
  $testInstance = getTestInstance($this->container, $this->config);

  $testInstance->handleError(
    E_DEPRECATED,
    'str_contains(): Passing null to parameter #2 ($needle) of type string is deprecated',
    '/home/user/laravel/routes/web.php',
    17
  );

  // This test just ensures no exception is thrown when logger is unresolvable
  expect(true)->toBeTrue();
});
