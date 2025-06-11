<?php

declare(strict_types=1);

use Laravel\Lumen\Exceptions\Handler;
use Mockery as m;

beforeEach(function () {
  $this->handler = new Handler();
});

afterEach(function () {
  m::close();
});

it('creates handler instance', function () {
  expect($this->handler)->toBeInstanceOf(Handler::class);
});

it('checks if exception should be reported', function () {
  $exception = new Exception('Test exception');

  $shouldReport = $this->handler->shouldReport($exception);

  expect($shouldReport)->toBeBool();
});

it('renders exception for console', function () {
  $exception = new Exception('Console exception');

  $this->handler->renderForConsole(new \Symfony\Component\Console\Output\NullOutput(), $exception);

  expect(true)->toBeTrue(); // If we get here, rendering was successful
});

it('reports exception', function () {
  $exception = new Exception('Report exception');

  $this->handler->report($exception);

  expect(true)->toBeTrue(); // If we get here, reporting was successful
});
