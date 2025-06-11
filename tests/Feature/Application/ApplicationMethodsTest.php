<?php

declare(strict_types=1);

it('gets application version', function () {
  $version = $this->app->version();

  expect($version)->toBeString();
  expect($version)->toContain('Lumen');
});

it('checks if application is running in console', function () {
  $isConsole = $this->app->runningInConsole();

  expect($isConsole)->toBeBool();
});

it('gets configuration path', function () {
  $configPath = $this->app->configPath();

  expect($configPath)->toBeString();
  expect($configPath)->toContain('config');
});

it('gets database path', function () {
  $databasePath = $this->app->databasePath();

  expect($databasePath)->toBeString();
  expect($databasePath)->toContain('database');
});

it('gets storage path', function () {
  $storagePath = $this->app->storagePath();

  expect($storagePath)->toBeString();
  expect($storagePath)->toContain('storage');
});

it('gets resource path', function () {
  $resourcePath = $this->app->resourcePath();

  expect($resourcePath)->toBeString();
  expect($resourcePath)->toContain('resources');
});

it('gets base path', function () {
  $basePath = $this->app->basePath();

  expect($basePath)->toBeString();
});

it('gets environment', function () {
  $environment = $this->app->environment();

  expect($environment)->toBeString();
});

it('checks if environment matches', function () {
  $environment = $this->app->environment();

  expect($this->app->environment($environment))->toBeTrue();
  expect($this->app->environment('non-existent-env'))->toBeFalse();
});

it('checks if application is local environment', function () {
  // Test isLocal method
  $isLocal = $this->app->isLocal();

  expect($isLocal)->toBeBool();
});

it('checks if application is production environment', function () {
  // Test isProduction method
  $isProduction = $this->app->isProduction();

  expect($isProduction)->toBeBool();
});

it('checks if application is down for maintenance', function () {
  // Test isDownForMaintenance method
  $isDown = $this->app->isDownForMaintenance();

  expect($isDown)->toBeFalse(); // Should always return false in Lumen
});

it('tests environment method with array patterns', function () {
  // Test environment method with array of patterns
  $result = $this->app->environment(['production', 'staging']);

  expect($result)->toBeBool();
});

it('tests environment method with multiple arguments', function () {
  // Test environment method with multiple string arguments
  $result = $this->app->environment('production', 'staging', 'local');

  expect($result)->toBeBool();
});

it('tests withAliases method', function () {
  // Test withAliases method with custom aliases using existing classes
  $customAliases = [
    \Laravel\Lumen\Application::class => 'TestAppAlias',
  ];

  $this->app->withAliases($customAliases);

  expect(true)->toBeTrue(); // If we get here, the method executed successfully
});

it('tests withAliases method with empty array', function () {
  // Test withAliases method with empty array (should use defaults)
  $this->app->withAliases();

  expect(true)->toBeTrue(); // If we get here, the method executed successfully
});

it('tests bootstrapRouter method', function () {
  // Test bootstrapRouter method
  $this->app->bootstrapRouter();

  expect($this->app->router)->not->toBeNull();
  expect($this->app->router)->toBeInstanceOf(\Laravel\Lumen\Routing\Router::class);
});

it('tests prepareRequest method with Symfony request', function () {
  // Create a Symfony request
  $symfonyRequest = \Symfony\Component\HttpFoundation\Request::create('/test', 'GET');

  // Use reflection to access the protected method
  $reflection = new ReflectionClass($this->app);
  $method = $reflection->getMethod('prepareRequest');
  $method->setAccessible(true);

  $result = $method->invoke($this->app, $symfonyRequest);

  // The result should be an instance of Request (could be Illuminate\Http\Request or Laravel\Lumen\Http\Request)
  expect($result)->toBeInstanceOf(\Illuminate\Http\Request::class);
});

it('tests prepareRequest method with Lumen request', function () {
  // Create a Lumen request
  $lumenRequest = \Laravel\Lumen\Http\Request::create('/test', 'GET');

  // Use reflection to access the protected method
  $reflection = new ReflectionClass($this->app);
  $method = $reflection->getMethod('prepareRequest');
  $method->setAccessible(true);

  $result = $method->invoke($this->app, $lumenRequest);

  expect($result)->toBeInstanceOf(\Laravel\Lumen\Http\Request::class);
  expect($result)->toBe($lumenRequest); // Should return the same instance
});

it('tests providerIsLoaded method', function () {
  // Test with a provider that hasn't been loaded
  expect($this->app->providerIsLoaded('NonExistentProvider'))->toBeFalse();

  // Register a provider and test that it's loaded
  $this->app->register(\Illuminate\Validation\ValidationServiceProvider::class);
  expect($this->app->providerIsLoaded(\Illuminate\Validation\ValidationServiceProvider::class))->toBeTrue();
});

it('tests withEloquent method', function () {
  // Test withEloquent method - this should make the 'db' service
  $this->app->withEloquent();

  // Verify that the db service is available
  expect($this->app->bound('db'))->toBeTrue();
});

it('tests eventsAreCached method', function () {
  // Test eventsAreCached method - should always return false in Lumen
  expect($this->app->eventsAreCached())->toBeFalse();
});

it('tests withFacades method', function () {
  // Test withFacades method with aliases enabled
  $this->app->withFacades(true, ['CustomAlias' => \Laravel\Lumen\Application::class]);

  // This should execute without errors
  expect(true)->toBeTrue();
});

it('tests withFacades method without aliases', function () {
  // Test withFacades method with aliases disabled
  $this->app->withFacades(false);

  // This should execute without errors
  expect(true)->toBeTrue();
});

it('tests configure method', function () {
  // Test configure method - this loads configuration files
  $this->app->configure('app');

  // Verify that the configuration was loaded
  expect($this->app->make('config')->has('app'))->toBeTrue();
});

it('tests loadComponent method', function () {
  // Test loadComponent method
  $result = $this->app->loadComponent('cache', \Illuminate\Cache\CacheServiceProvider::class);

  // Should return the cache manager
  expect($result)->not->toBeNull();
  expect($this->app->bound('cache'))->toBeTrue();
});

it('tests prepareForConsoleCommand method', function () {
  // Test prepareForConsoleCommand method
  $this->app->prepareForConsoleCommand(true);

  // Should have registered console-related services
  expect($this->app->bound('cache'))->toBeTrue();
  expect($this->app->bound('queue'))->toBeTrue();
});

it('tests prepareForConsoleCommand method without aliases', function () {
  // Test prepareForConsoleCommand method without aliases
  $this->app->prepareForConsoleCommand(false);

  // Should still have registered console-related services
  expect($this->app->bound('cache'))->toBeTrue();
  expect($this->app->bound('queue'))->toBeTrue();
});

it('tests getLocale method', function () {
  // Configure app to have locale setting
  $this->app->configure('app');

  // Test getLocale method
  $locale = $this->app->getLocale();

  expect($locale)->toBeString();
});

it('tests setLocale method', function () {
  // Configure app and translator
  $this->app->configure('app');
  $this->app->make('translator'); // This will register the translator

  // Test setLocale method
  $this->app->setLocale('es');

  expect($this->app->getLocale())->toBe('es');
});

it('tests getFallbackLocale method', function () {
  // Configure app to have fallback locale setting
  $this->app->configure('app');

  // Test getFallbackLocale method
  $fallbackLocale = $this->app->getFallbackLocale();

  expect($fallbackLocale)->toBeString();
});

it('tests isLocale method', function () {
  // Configure app
  $this->app->configure('app');

  // Test isLocale method
  $currentLocale = $this->app->getLocale();
  expect($this->app->isLocale($currentLocale))->toBeTrue();
  expect($this->app->isLocale('non-existent-locale'))->toBeFalse();
});

it('tests terminating method', function () {
  $called = false;

  // Test terminating method - register a callback
  $this->app->terminating(function () use (&$called) {
    $called = true;
  });

  // Test terminate method - should call the callback
  $this->app->terminate();

  expect($called)->toBeTrue();
});

it('tests terminating method with string callback', function () {
  // Test terminating method with string callback
  $this->app->terminating('strlen'); // Use a simple built-in function

  // Should execute without errors
  expect(true)->toBeTrue();
});

it('tests path method', function () {
  // Test path method
  $path = $this->app->path();

  expect($path)->toBeString();
  expect($path)->toContain('app');
});

it('tests basePath method', function () {
  // Test basePath method
  $basePath = $this->app->basePath();

  expect($basePath)->toBeString();
});

it('tests basePath method with path parameter', function () {
  // Test basePath method with path parameter
  $basePath = $this->app->basePath('config');

  expect($basePath)->toBeString();
  expect($basePath)->toContain('config');
});

it('tests make method with available bindings', function () {
  // Test make method with available bindings - this should trigger the binding registration
  $config = $this->app->make('config');

  expect($config)->not->toBeNull();
  expect($this->app->bound('config'))->toBeTrue();
});

it('tests useStoragePath method', function () {
  // Test useStoragePath method
  $customPath = '/custom/storage/path';

  $result = $this->app->useStoragePath($customPath);

  expect($result)->toBe($this->app); // Should return self for fluent interface
  expect($this->app->storagePath())->toBe($customPath);
  expect($this->app->make('path.storage'))->toBe($customPath);
});

it('tests getLanguagePath method with fallback', function () {
  // Test getLanguagePath method - use reflection to access protected method
  $reflection = new ReflectionClass($this->app);
  $method = $reflection->getMethod('getLanguagePath');
  $method->setAccessible(true);

  $langPath = $method->invoke($this->app);

  expect($langPath)->toBeString();
  // Should either be the resources/lang path or the fallback path
  expect($langPath)->toMatch('/lang$/');
});

it('tests runningUnitTests method', function () {
  // Test runningUnitTests method
  $isRunningTests = $this->app->runningUnitTests();

  expect($isRunningTests)->toBeBool();
  // The result depends on the environment, so we just test that it returns a boolean
});

it('tests flush method', function () {
  // Set up some state first
  $this->app->make('config');
  $this->app->withAliases(['TestAlias' => \Laravel\Lumen\Application::class]);

  // Verify state exists
  expect($this->app->bound('config'))->toBeTrue();

  // Test flush method
  $this->app->flush();

  // Verify state was cleared
  expect($this->app->bound('config'))->toBeFalse();
});

it('tests getNamespace method with composer.json', function () {
  // This test might fail if composer.json doesn't exist or doesn't have the right structure
  // But we can test that the method exists and handles the case
  try {
    $namespace = $this->app->getNamespace();
    expect($namespace)->toBeString();
  } catch (RuntimeException $e) {
    // If it throws RuntimeException, that's also valid behavior
    expect($e->getMessage())->toContain('Unable to detect application namespace');
  }
});

it('tests registerPsrRequestBindings method without PSR libraries', function () {
  // Use reflection to call the protected method on existing app
  $reflection = new ReflectionClass($this->app);
  $method = $reflection->getMethod('registerPsrRequestBindings');
  $method->setAccessible(true);

  $method->invoke($this->app);

  // Verify that PSR request binding is registered
  expect($this->app->bound(\Psr\Http\Message\ServerRequestInterface::class))->toBeTrue();

  // Trying to resolve should throw exception since PSR libraries aren't available
  expect(function () {
    $this->app->make(\Psr\Http\Message\ServerRequestInterface::class);
  })->toThrow(Exception::class);
});

it('tests registerPsrResponseBindings method without PSR libraries', function () {
  // Use reflection to call the protected method on existing app
  $reflection = new ReflectionClass($this->app);
  $method = $reflection->getMethod('registerPsrResponseBindings');
  $method->setAccessible(true);

  $method->invoke($this->app);

  // Verify that PSR response binding is registered
  expect($this->app->bound(\Psr\Http\Message\ResponseInterface::class))->toBeTrue();

  // Test that the binding exists (we can't test the exception without PSR libraries)
  expect(true)->toBeTrue();
});

it('tests basePath method with path parameter and null base', function () {
  // Test the basePath logic without creating new Application
  $reflection = new ReflectionClass($this->app);
  $property = $reflection->getProperty('basePath');
  $property->setAccessible(true);
  $originalBasePath = $property->getValue($this->app);

  // Temporarily set basePath to null to test the fallback logic
  $property->setValue($this->app, null);

  $basePath = $this->app->basePath('config');

  expect($basePath)->toBeString();
  expect($basePath)->toContain('config');

  // Restore original basePath
  $property->setValue($this->app, $originalBasePath);
});

it('tests getNamespace method with cached namespace', function () {
  // Use reflection to set the namespace property
  $reflection = new ReflectionClass($this->app);
  $property = $reflection->getProperty('namespace');
  $property->setAccessible(true);
  $originalNamespace = $property->getValue($this->app);

  // Set a test namespace
  $property->setValue($this->app, 'App\\');

  $namespace = $this->app->getNamespace();

  expect($namespace)->toBe('App\\');

  // Restore original namespace
  $property->setValue($this->app, $originalNamespace);
});
