<?php

declare(strict_types=1);

use Laravel\Lumen\Application;





it('registers exception handlers', function () {
    // Test that the RegistersExceptionHandlers trait has the expected methods
    $reflection = new ReflectionClass('Laravel\Lumen\Concerns\RegistersExceptionHandlers');

    // Check that the trait has the registerErrorHandling method
    expect($reflection->hasMethod('registerErrorHandling'))->toBeTrue();

    // Check that the trait has the handleError method
    expect($reflection->hasMethod('handleError'))->toBeTrue();

    // Check that the trait has the handleException method
    expect($reflection->hasMethod('handleException'))->toBeTrue();

    expect(true)->toBeTrue(); // If we get here, all methods exist
});

it('configures error handling', function () {
    // Test that the Application class uses the RegistersExceptionHandlers trait
    $reflection = new ReflectionClass(Application::class);
    $traits = $reflection->getTraitNames();

    expect($traits)->toContain('Laravel\Lumen\Concerns\RegistersExceptionHandlers');
    expect(true)->toBeTrue(); // Test passes if trait is used
});

it('tests abort method with 404 code', function () {
    // Test abort method with 404 code - should throw NotFoundHttpException
    expect(function () {
        $this->app->abort(404, 'Page not found');
    })->toThrow(\Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class);
});

it('tests abort method with other codes', function () {
    // Test abort method with non-404 code - should throw HttpException
    expect(function () {
        $this->app->abort(500, 'Server error', ['X-Custom' => 'header']);
    })->toThrow(\Symfony\Component\HttpKernel\Exception\HttpException::class);
});

it('tests handleError method with deprecation', function () {
    // Test handleError method with deprecation level
    $level = E_DEPRECATED;
    $message = 'This function is deprecated';
    $file = '/test/file.php';
    $line = 123;

    // This should handle deprecation without throwing
    $result = $this->app->handleError($level, $message, $file, $line);

    expect($result)->toBeNull();
});

it('tests handleError method with suppressed error', function () {
    // Test handleError method with suppressed error (error_reporting returns 0)
    $originalReporting = error_reporting(0); // Suppress all errors

    try {
        $result = $this->app->handleError(E_WARNING, 'Warning message', '/test/file.php', 789);
        expect($result)->toBeNull();
    } finally {
        error_reporting($originalReporting); // Restore original error reporting
    }
});

it('tests isDeprecation method', function () {
    // Test isDeprecation method using reflection
    $reflection = new ReflectionClass($this->app);
    $method = $reflection->getMethod('isDeprecation');
    $method->setAccessible(true);

    expect($method->invoke($this->app, E_DEPRECATED))->toBeTrue();
    expect($method->invoke($this->app, E_USER_DEPRECATED))->toBeTrue();
    expect($method->invoke($this->app, E_ERROR))->toBeFalse();
    expect($method->invoke($this->app, E_WARNING))->toBeFalse();
});

it('tests isFatal method', function () {
    // Test isFatal method using reflection
    $reflection = new ReflectionClass($this->app);
    $method = $reflection->getMethod('isFatal');
    $method->setAccessible(true);

    expect($method->invoke($this->app, E_ERROR))->toBeTrue();
    expect($method->invoke($this->app, E_CORE_ERROR))->toBeTrue();
    expect($method->invoke($this->app, E_COMPILE_ERROR))->toBeTrue();
    expect($method->invoke($this->app, E_PARSE))->toBeTrue();
    expect($method->invoke($this->app, E_WARNING))->toBeFalse();
    expect($method->invoke($this->app, E_NOTICE))->toBeFalse();
});

it('tests fatalErrorFromPhpError method', function () {
    // Test fatalErrorFromPhpError method using reflection
    $reflection = new ReflectionClass($this->app);
    $method = $reflection->getMethod('fatalErrorFromPhpError');
    $method->setAccessible(true);

    $error = [
        'type' => E_ERROR,
        'message' => 'Fatal error',
        'file' => '/test/file.php',
        'line' => 123
    ];

    $exception = $method->invoke($this->app, $error, 0);

    expect($exception)->toBeInstanceOf(\Symfony\Component\ErrorHandler\Error\FatalError::class);
});

it('tests ensureDeprecationLoggerIsConfigured method', function () {
    // Test ensureDeprecationLoggerIsConfigured method using reflection
    $reflection = new ReflectionClass($this->app);
    $method = $reflection->getMethod('ensureDeprecationLoggerIsConfigured');
    $method->setAccessible(true);

    // Configure logging first
    $this->app->configure('logging');

    // This should execute without error
    $method->invoke($this->app);

    expect(true)->toBeTrue();
});

it('tests resolveExceptionHandler method with bound handler', function () {
    // Test resolveExceptionHandler method with custom handler
    $customHandler = \Mockery::mock(\Illuminate\Contracts\Debug\ExceptionHandler::class);
    $this->app->instance(\Illuminate\Contracts\Debug\ExceptionHandler::class, $customHandler);

    $reflection = new ReflectionClass($this->app);
    $method = $reflection->getMethod('resolveExceptionHandler');
    $method->setAccessible(true);

    $handler = $method->invoke($this->app);

    expect($handler)->toBe($customHandler);
});

it('tests resolveExceptionHandler method with default handler', function () {
    // Test resolveExceptionHandler method with default handler
    $reflection = new ReflectionClass($this->app);
    $method = $reflection->getMethod('resolveExceptionHandler');
    $method->setAccessible(true);

    $handler = $method->invoke($this->app);

    expect($handler)->toBeInstanceOf(\Laravel\Lumen\Exceptions\Handler::class);
});
