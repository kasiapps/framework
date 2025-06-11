<?php

declare(strict_types=1);

use Laravel\Lumen\Exceptions\Handler;

it('creates handler instance', function () {
  $handler = new Handler();
  expect($handler)->toBeInstanceOf(Handler::class);
});

it('checks if exception should be reported', function () {
  $handler = new Handler();
  $exception = new Exception('Test exception');

  $shouldReport = $handler->shouldReport($exception);

  expect($shouldReport)->toBeBool();
  expect($shouldReport)->toBeTrue(); // By default, exceptions should be reported
});

it('renders exception for console', function () {
  $handler = new Handler();
  $exception = new Exception('Console exception');

  $handler->renderForConsole(new \Symfony\Component\Console\Output\NullOutput(), $exception);

  expect(true)->toBeTrue(); // If we get here, rendering was successful
});

it('tests shouldntReport method', function () {
  $handler = new Handler();

  // Use reflection to access the protected method
  $reflection = new ReflectionClass($handler);
  $method = $reflection->getMethod('shouldntReport');
  $method->setAccessible(true);

  $exception = new Exception('Test exception');
  $result = $method->invoke($handler, $exception);

  expect($result)->toBeFalse(); // Exception should be reported by default
});

it('tests shouldntReport with dontReport list', function () {
  // Create a handler with custom dontReport list
  $handler = new class extends Handler {
    protected $dontReport = [
      \InvalidArgumentException::class,
    ];
  };

  // Use reflection to access the protected method
  $reflection = new ReflectionClass($handler);
  $method = $reflection->getMethod('shouldntReport');
  $method->setAccessible(true);

  $reportedException = new Exception('Should be reported');
  $ignoredException = new InvalidArgumentException('Should not be reported');

  expect($method->invoke($handler, $reportedException))->toBeFalse();
  expect($method->invoke($handler, $ignoredException))->toBeTrue();
});

it('tests isHttpException helper method', function () {
  $handler = new Handler();

  // Use reflection to access the protected method
  $reflection = new ReflectionClass($handler);
  $method = $reflection->getMethod('isHttpException');
  $method->setAccessible(true);

  $httpException = new \Symfony\Component\HttpKernel\Exception\HttpException(404, 'Not found');
  $regularException = new Exception('Regular exception');

  expect($method->invoke($handler, $httpException))->toBeTrue();
  expect($method->invoke($handler, $regularException))->toBeFalse();
});

it('tests Handler class structure', function () {
  $handler = new Handler();
  $reflection = new ReflectionClass($handler);

  // Test that all expected methods exist
  $expectedMethods = [
    'report',
    'shouldReport',
    'shouldntReport',
    'render',
    'prepareJsonResponse',
    'convertExceptionToArray',
    'prepareResponse',
    'renderExceptionWithSymfony',
    'renderForConsole',
    'isHttpException'
  ];

  foreach ($expectedMethods as $method) {
    expect($reflection->hasMethod($method))->toBeTrue();
  }

  // Test that dontReport property exists
  expect($reflection->hasProperty('dontReport'))->toBeTrue();
});
