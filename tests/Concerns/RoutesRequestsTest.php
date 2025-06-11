<?php

declare(strict_types=1);

use Laravel\Lumen\Application;
use Laravel\Lumen\Concerns\RoutesRequests;

afterEach(function () {
    // Restore error handlers to prevent warnings
    restore_error_handler();
    restore_exception_handler();
});

it('checks if middleware should be skipped', function () {
  $app = new class extends Application
  {
    use RoutesRequests;

    public function callShouldSkipMiddleware()
    {
      return $this->shouldSkipMiddleware();
    }
  };

  $shouldSkip = $app->callShouldSkipMiddleware();

  expect($shouldSkip)->toBeBool();
});

it('has routes requests trait', function () {
  $app = new Application();

  expect($app)->toBeInstanceOf(Application::class);
});
