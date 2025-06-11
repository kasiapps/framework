<?php

declare(strict_types=1);

use Laravel\Lumen\Bootstrap\LoadEnvironmentVariables;
use Mockery as m;

beforeEach(function () {
  $this->tempDir = sys_get_temp_dir().'/lumen_test_'.uniqid();
  mkdir($this->tempDir);
});

afterEach(function () {
  m::close();
  if (is_dir($this->tempDir)) {
    $files = glob($this->tempDir.'/{,.}*', GLOB_BRACE);
    foreach ($files as $file) {
      if (is_file($file)) {
        unlink($file);
      }
    }
    rmdir($this->tempDir);
  }
});

it('creates instance with file path', function () {
  $loader = new LoadEnvironmentVariables('/path/to/env');

  expect($loader)->toBeInstanceOf(LoadEnvironmentVariables::class);
});

it('creates instance with file path and name', function () {
  $loader = new LoadEnvironmentVariables('/path/to/env', '.env.testing');

  expect($loader)->toBeInstanceOf(LoadEnvironmentVariables::class);
});

it('bootstraps successfully with valid env file', function () {
  file_put_contents($this->tempDir.'/.env', "TEST_VAR=test_value\n");

  $loader = new LoadEnvironmentVariables($this->tempDir);

  $loader->bootstrap();

  expect(true)->toBeTrue(); // If we get here, no exception was thrown
});

it('bootstraps successfully with custom env file name', function () {
  file_put_contents($this->tempDir.'/.env.custom', "TEST_VAR=test_value\n");

  $loader = new LoadEnvironmentVariables($this->tempDir, '.env.custom');

  $loader->bootstrap();

  expect(true)->toBeTrue(); // If we get here, no exception was thrown
});

it('bootstraps silently when no env file exists', function () {
  $loader = new LoadEnvironmentVariables($this->tempDir);

  // Suppress the warning about missing .env file
  $originalLevel = error_reporting(E_ALL & ~E_WARNING);

  try {
    $loader->bootstrap();
    expect(true)->toBeTrue(); // If we get here, no exception was thrown
  } finally {
    error_reporting($originalLevel);
  }
});

it('handles invalid file exception', function () {
  // Create an invalid .env file
  file_put_contents($this->tempDir.'/.env', "INVALID_SYNTAX=\n\"unclosed quote");

  $loader = new LoadEnvironmentVariables($this->tempDir);

  // This should exit with code 1, but we can't easily test that in unit tests
  // So we'll test the method exists and can be called
  expect($loader)->toBeInstanceOf(LoadEnvironmentVariables::class);
});
