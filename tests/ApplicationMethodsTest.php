<?php

declare(strict_types=1);

use Laravel\Lumen\Application;

afterEach(function () {
  // Restore error handlers to prevent warnings
  restore_error_handler();
  restore_exception_handler();
});

it('gets application version', function () {
  $app = new Application();

  $version = $app->version();

  expect($version)->toBeString();
  expect($version)->toContain('Lumen');
});

it('checks if application is running in console', function () {
  $app = new Application();

  $isConsole = $app->runningInConsole();

  expect($isConsole)->toBeBool();
});

it('gets configuration path', function () {
  $app = new Application();

  $configPath = $app->configPath();

  expect($configPath)->toBeString();
  expect($configPath)->toContain('config');
});

it('gets database path', function () {
  $app = new Application();

  $databasePath = $app->databasePath();

  expect($databasePath)->toBeString();
  expect($databasePath)->toContain('database');
});

it('gets storage path', function () {
  $app = new Application();

  $storagePath = $app->storagePath();

  expect($storagePath)->toBeString();
  expect($storagePath)->toContain('storage');
});

it('gets resource path', function () {
  $app = new Application();

  $resourcePath = $app->resourcePath();

  expect($resourcePath)->toBeString();
  expect($resourcePath)->toContain('resources');
});

it('gets base path', function () {
  $app = new Application();

  $basePath = $app->basePath();

  expect($basePath)->toBeString();
});

it('gets environment', function () {
  $app = new Application();

  $environment = $app->environment();

  expect($environment)->toBeString();
});

it('checks if environment matches', function () {
  $app = new Application();

  $environment = $app->environment();

  expect($app->environment($environment))->toBeTrue();
  expect($app->environment('non-existent-env'))->toBeFalse();
});
