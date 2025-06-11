<?php

declare(strict_types=1);

use Laravel\Lumen\Application;
use Laravel\Lumen\Console\ConsoleServiceProvider;

afterEach(function () {
    // Restore error handlers to prevent warnings
    restore_error_handler();
    restore_exception_handler();
});

it('registers console service provider', function () {
  $app = new Application();
  $provider = new ConsoleServiceProvider($app);

  expect($provider)->toBeInstanceOf(ConsoleServiceProvider::class);

  $provider->register();

  expect(true)->toBeTrue(); // If we get here, registration was successful
});

it('provides console service provider', function () {
  $app = new Application();
  $provider = new ConsoleServiceProvider($app);

  $provides = $provider->provides();

  expect($provides)->toBeArray();
});
