<?php

namespace Tests;

use Illuminate\Bus\Dispatcher;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Contracts\Validation\Factory as ValidationFactory;
use Illuminate\Events\Dispatcher as EventDispatcher;
use Laravel\Lumen\Application;
use Laravel\Lumen\Testing\TestCase as BaseTestCase;
use Mockery as m;

abstract class TestCase extends BaseTestCase
{
  /**
   * CREATES THE APPLICATION.
   *
   * @return \Laravel\Lumen\Application
   */
  public function createApplication()
  {
    $application = new Application();

    // Configure basic services needed for testing
    $this->configureBasicServices($application);
    $this->configureMockServices($application);

    return $application;
  }

  /**
   * Configure basic services that most tests need.
   */
  protected function configureBasicServices(Application $app): void
  {
    // Config service
    $app->singleton('config', function () {
      return new class
      {
        private array $config = [
          'app.url' => 'http://localhost',
          'app.debug' => true,
          'app.env' => 'testing',
        ];

        public function get(string $key, $default = null)
        {
          return $this->config[$key] ?? $default;
        }

        public function set(string $key, $value): void
        {
          $this->config[$key] = $value;
        }
      };
    });

    // URL service
    $app->singleton('url', function () {
      return new class
      {
        public function forceRootUrl(string $url): void
        {
          // Mock implementation - parameter used for interface compliance
          unset($url);
        }
      };
    });

    // Request service
    $app->singleton('request', function () {
      return \Laravel\Lumen\Http\Request::create('http://localhost', 'GET');
    });
  }

  /**
   * Configure mock services that tests commonly need.
   */
  protected function configureMockServices(Application $app): void
  {
    // Events service
    $app->singleton('events', function () {
      return m::mock(EventDispatcher::class)->makePartial();
    });

    // Validator service
    $app->singleton('validator', function () {
      return m::mock(ValidationFactory::class);
    });

    // Gate service
    $app->singleton(Gate::class, function () {
      return m::mock(Gate::class);
    });

    // Bus dispatcher service
    $app->singleton(Dispatcher::class, function () {
      return m::mock(Dispatcher::class);
    });

    // Auth service
    $app->singleton('auth', function () {
      return m::mock(AuthFactory::class);
    });
  }
}
