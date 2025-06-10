<?php

declare(strict_types=1);

use Laravel\Lumen\Console\Kernel as ConsoleKernel;

it('has reroute symfony command events method', function () {
  // Test that the ConsoleKernel class has the rerouteSymfonyCommandEvents method
  expect(method_exists(ConsoleKernel::class, 'rerouteSymfonyCommandEvents'))->toBeTrue();

  // Test that the method is public
  $reflection = new ReflectionMethod(ConsoleKernel::class, 'rerouteSymfonyCommandEvents');
  expect($reflection->isPublic())->toBeTrue();

  // Test that the method returns static (fluent interface)
  expect($reflection->getReturnType()?->getName())->toBe('static');
});
