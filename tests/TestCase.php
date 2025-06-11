<?php

namespace Tests;

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
    // Create a REAL Application instance for proper testing
    // We need to create it without triggering error handler registration
    $application = new class extends Application {
      public function __construct($basePath = null) {
        // Call parent constructor but skip error handler registration
        $this->basePath = $basePath;
        $this->bootstrapContainer();
        // Skip: $this->registerErrorHandling(); - this causes risky test warnings
        $this->bootstrapRouter();
      }
    };

    // Configure minimal real services needed for testing
    $this->configureRealServices($application);

    return $application;
  }

  /**
   * Configure real services that tests need.
   */
  protected function configureRealServices(Application $app): void
  {
    // Set up basic configuration
    $app->configure('app');

    // Only mock services that would cause external dependencies or side effects
    // Keep most services real for proper testing

    // Mock only the logger to prevent file I/O during tests
    $app->singleton(\Psr\Log\LoggerInterface::class, function () {
      return m::mock(\Psr\Log\LoggerInterface::class)->shouldIgnoreMissing();
    });
  }
}
