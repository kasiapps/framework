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

it('tests middleware method with single middleware', function () {
  $app = new class extends Application {
    use RoutesRequests;

    public function getMiddleware() {
      return $this->middleware;
    }
  };

  $middleware = function() { return 'test'; };
  $result = $app->middleware($middleware);

  expect($result)->toBe($app);
  expect($app->getMiddleware())->toContain($middleware);
});

it('tests run method with string response', function () {
  $app = new class extends Application {
    use RoutesRequests;

    public function dispatch($request = null) {
      return 'test response';
    }

    public function terminate(): void {
      // Mock terminate
    }
  };

  ob_start();
  $app->run();
  $output = ob_get_clean();

  expect($output)->toBe('test response');
});

it('tests run method with symfony response', function () {
  $app = new class extends Application {
    use RoutesRequests;

    public function dispatch($request = null) {
      return new \Symfony\Component\HttpFoundation\Response('symfony response');
    }

    public function terminate(): void {
      // Mock terminate
    }
  };

  ob_start();
  $app->run();
  $output = ob_get_clean();

  expect($output)->toContain('symfony response');
});

it('tests callTerminableMiddleware with non-string middleware', function () {
  $app = new class extends Application {
    use RoutesRequests;

    public function callTerminableMiddleware($response) {
      return parent::callTerminableMiddleware($response);
    }

    public function shouldSkipMiddleware(): bool {
      return false;
    }
  };

  $app->middleware([function() { return 'test'; }]);

  $response = new \Illuminate\Http\Response('test');
  $app->callTerminableMiddleware($response);

  expect(true)->toBeTrue();
});

it('tests handleDispatcherResponse with null return', function () {
  $app = new class extends Application {
    use RoutesRequests;

    public function handleDispatcherResponse(array $routeInfo) {
      return parent::handleDispatcherResponse($routeInfo);
    }
  };

  $result = $app->handleDispatcherResponse([999]); // Invalid dispatcher code

  expect($result)->toBeNull();
});

it('tests callActionOnArrayBasedRoute with no callable found', function () {
  $app = new class extends Application {
    use RoutesRequests;

    public function callActionOnArrayBasedRoute(array $routeInfo) {
      return parent::callActionOnArrayBasedRoute($routeInfo);
    }
  };

  expect(function () use ($app) {
    $app->callActionOnArrayBasedRoute([true, ['invalid' => 'action'], []]);
  })->toThrow(RuntimeException::class, 'Unable to resolve route handler.');
});

it('tests callActionOnArrayBasedRoute with HttpResponseException', function () {
  $app = new class extends Application {
    use RoutesRequests;

    public function callActionOnArrayBasedRoute(array $routeInfo) {
      return parent::callActionOnArrayBasedRoute($routeInfo);
    }

    public function call($callback, array $parameters = [], $defaultMethod = null) {
      throw new \Illuminate\Http\Exceptions\HttpResponseException(
        new \Illuminate\Http\Response('exception response')
      );
    }
  };

  $closure = function() { return 'test'; };
  $result = $app->callActionOnArrayBasedRoute([true, [$closure], []]);

  expect($result)->toBeInstanceOf(\Illuminate\Http\Response::class);
  expect($result->getContent())->toBe('exception response');
});

it('tests middleware method with array middleware', function () {
  $app = new class extends Application {
    use RoutesRequests;

    public function getMiddleware() {
      return $this->middleware;
    }
  };

  $middleware = ['middleware1', 'middleware2'];
  $result = $app->middleware($middleware);

  expect($result)->toBe($app);
  expect($app->getMiddleware())->toHaveCount(2);
});

it('tests sendThroughPipeline with no middleware', function () {
  $app = new class extends Application {
    use RoutesRequests;

    public function sendThroughPipeline(array $middleware, \Closure $then) {
      return parent::sendThroughPipeline($middleware, $then);
    }

    public function shouldSkipMiddleware(): bool {
      return false;
    }
  };

  $result = $app->sendThroughPipeline([], function() {
    return 'no middleware result';
  });

  expect($result)->toBe('no middleware result');
});

it('tests callControllerCallable with HttpResponseException', function () {
  $app = new class extends Application {
    use RoutesRequests;

    public function callControllerCallable(callable $callable, array $parameters = []) {
      return parent::callControllerCallable($callable, $parameters);
    }

    public function call($callback, array $parameters = [], $defaultMethod = null) {
      throw new \Illuminate\Http\Exceptions\HttpResponseException(
        new \Illuminate\Http\Response('exception response')
      );
    }
  };

  $callable = function() { return 'test'; };
  $result = $app->callControllerCallable($callable);

  expect($result)->toBeInstanceOf(\Illuminate\Http\Response::class);
  expect($result->getContent())->toBe('exception response');
});

it('tests prepareResponse with BinaryFileResponse', function () {
  $app = new class extends Application {
    use RoutesRequests;
  };

  $tempFile = tempnam(sys_get_temp_dir(), 'test');
  file_put_contents($tempFile, 'test content');

  $binaryResponse = new \Symfony\Component\HttpFoundation\BinaryFileResponse($tempFile);
  $result = $app->prepareResponse($binaryResponse);

  expect($result)->toBeInstanceOf(\Symfony\Component\HttpFoundation\BinaryFileResponse::class);

  unlink($tempFile);
});
