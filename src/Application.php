<?php

declare(strict_types=1);

namespace Laravel\Lumen;

use Illuminate\Auth\AuthManager;
use Illuminate\Auth\AuthServiceProvider;
use Illuminate\Broadcasting\BroadcastServiceProvider;
use Illuminate\Bus\BusServiceProvider;
use Illuminate\Cache\CacheServiceProvider;
use Illuminate\Config\Repository as ConfigRepository;
use Illuminate\Container\Container;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Broadcasting\Broadcaster;
use Illuminate\Contracts\Broadcasting\Factory;
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Encryption\Encrypter;
use Illuminate\Contracts\Filesystem\Cloud;
use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Contracts\Translation\Translator;
use Illuminate\Database\ConnectionResolverInterface;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\DatabaseServiceProvider;
use Illuminate\Database\MigrationServiceProvider;
use Illuminate\Encryption\EncryptionServiceProvider;
use Illuminate\Events\EventServiceProvider;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Filesystem\FilesystemServiceProvider;
use Illuminate\Hashing\HashServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Log\LogManager;
use Illuminate\Pagination\PaginationServiceProvider;
use Illuminate\Queue\QueueServiceProvider;
use Illuminate\Redis\Connections\Connection;
use Illuminate\Redis\RedisManager;
use Illuminate\Support\Composer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Illuminate\Translation\TranslationServiceProvider;
use Illuminate\Validation\ValidationServiceProvider;
use Illuminate\View\ViewServiceProvider;
use Laravel\Lumen\Concerns\RegistersExceptionHandlers;
use Laravel\Lumen\Concerns\RoutesRequests;
use Laravel\Lumen\Console\ConsoleServiceProvider;
use Laravel\Lumen\Routing\Router;
use Laravel\Lumen\Routing\UrlGenerator;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Response as PsrResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Symfony\Bridge\PsrHttpMessage\Factory\PsrHttpFactory;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

class Application extends Container
{
  use RegistersExceptionHandlers,
    RoutesRequests;

  /**
   * Indicates if the class aliases have been registered.
   *
   * @var bool
   */
  protected static $aliasesRegistered = false;

  /**
   * All of the loaded configuration files.
   *
   * @var array
   */
  protected $loadedConfigurations = [];

  /**
   * Indicates if the application has "booted".
   *
   * @var bool
   */
  protected $booted = false;

  /**
   * The loaded service providers.
   *
   * @var array
   */
  protected $loadedProviders = [];

  /**
   * The service binding methods that have been executed.
   *
   * @var array
   */
  protected $ranServiceBinders = [];

  /**
   * The custom storage path defined by the developer.
   *
   * @var string
   */
  protected $storagePath;

  /**
   * The application namespace.
   *
   * @var string
   */
  protected $namespace;

  /**
   * The Router instance.
   *
   * @var Router
   */
  public $router;

  /**
   * The array of terminating callbacks.
   *
   * @var callable[]
   */
  protected $terminatingCallbacks = [];

  /**
   * Create a new Lumen application instance.
   *
   * @param  string|null  $basePath
   * @return void
   */
  public function __construct(/**
   * The base path of the application installation.
   */
    protected $basePath = null)
  {
    $this->bootstrapContainer();
    $this->registerErrorHandling();
    $this->bootstrapRouter();
  }

  /**
   * Bootstrap the application container.
   *
   * @return void
   */
  protected function bootstrapContainer()
  {
    static::setInstance($this);

    $this->instance('app', $this);
    $this->instance(self::class, $this);

    $this->instance('path', $this->path());

    $this->instance('env', $this->environment());

    $this->registerContainerAliases();
  }

  /**
   * Bootstrap the router instance.
   */
  public function bootstrapRouter(): void
  {
    $this->router = new Router($this);
  }

  /**
   * Get the version number of the application.
   */
  public function version(): string
  {
    return 'Lumen (11.1.0) (Laravel Components ^11.0)';
  }

  /**
   * Determine if the application is currently down for maintenance.
   */
  public function isDownForMaintenance(): bool
  {
    return false;
  }

  /**
   * Get or check the current application environment.
   *
   * @param  mixed
   * @return string
   */
  public function environment()
  {
    $env = env('APP_ENV', config('app.env', 'production'));

    if (func_num_args() > 0) {
      $patterns = is_array(func_get_arg(0)) ? func_get_arg(0) : func_get_args();

      foreach ($patterns as $pattern) {
        if (Str::is($pattern, $env)) {
          return true;
        }
      }

      return false;
    }

    return $env;
  }

  /**
   * Determine if the application is in the local environment.
   */
  public function isLocal(): bool
  {
    return $this->environment() === 'local';
  }

  /**
   * Determine if the application is in the production environment.
   */
  public function isProduction(): bool
  {
    return $this->environment() === 'production';
  }

  /**
   * Determine if the given service provider is loaded.
   */
  public function providerIsLoaded(string $provider): bool
  {
    return isset($this->loadedProviders[$provider]);
  }

  /**
   * Register a service provider with the application.
   *
   * @param  ServiceProvider|string  $provider
   */
  public function register($provider): void
  {
    if (! $provider instanceof ServiceProvider) {
      $provider = new $provider($this);
    }

    if (array_key_exists($providerName = $provider::class, $this->loadedProviders)) {
      return;
    }

    $this->loadedProviders[$providerName] = $provider;

    if (method_exists($provider, 'register')) {
      $provider->register();
    }

    if ($this->booted) {
      $this->bootProvider($provider);
    }
  }

  /**
   * Register a deferred provider and service.
   *
   * @param  string  $provider
   */
  public function registerDeferredProvider($provider): void
  {
    $this->register($provider);
  }

  /**
   * Boots the registered providers.
   */
  public function boot(): void
  {
    if ($this->booted) {
      return;
    }

    foreach ($this->loadedProviders as $loadedProvider) {
      $this->bootProvider($loadedProvider);
    }

    $this->booted = true;
  }

  /**
   * Boot the given service provider.
   *
   * @return mixed
   */
  protected function bootProvider(ServiceProvider $serviceProvider)
  {
    if (method_exists($serviceProvider, 'boot')) {
      return $this->call([$serviceProvider, 'boot']);
    }

    return null;
  }

  /**
   * Resolve the given type from the container.
   *
   * @param  string  $abstract
   * @return mixed
   */
  public function make($abstract, array $parameters = [])
  {
    $abstract = $this->getAlias($abstract);

    if (! $this->bound($abstract) &&
        array_key_exists($abstract, $this->availableBindings) &&
        ! array_key_exists($this->availableBindings[$abstract], $this->ranServiceBinders)) {
      $this->{$method = $this->availableBindings[$abstract]}();

      $this->ranServiceBinders[$method] = true;
    }

    return parent::make($abstract, $parameters);
  }

  /**
   * Register container bindings for the application.
   *
   * @return void
   */
  protected function registerAuthBindings()
  {
    $this->singleton('auth', fn () => $this->loadComponent('auth', AuthServiceProvider::class, 'auth'));

    $this->singleton('auth.driver', fn () => $this->loadComponent('auth', AuthServiceProvider::class, 'auth.driver'));

    $this->singleton(AuthManager::class, fn () => $this->loadComponent('auth', AuthServiceProvider::class, 'auth'));

    $this->singleton(Gate::class, fn () => $this->loadComponent('auth', AuthServiceProvider::class, Gate::class));
  }

  /**
   * Register container bindings for the application.
   *
   * @return void
   */
  protected function registerBroadcastingBindings()
  {
    $this->singleton(Factory::class, fn () => $this->loadComponent('broadcasting', BroadcastServiceProvider::class, Factory::class));

    $this->singleton(Broadcaster::class, fn () => $this->loadComponent('broadcasting', BroadcastServiceProvider::class, Broadcaster::class));
  }

  /**
   * Register container bindings for the application.
   *
   * @return void
   */
  protected function registerBusBindings()
  {
    $this->singleton(Dispatcher::class, function () {
      $this->register(BusServiceProvider::class);

      return $this->make(Dispatcher::class);
    });
  }

  /**
   * Register container bindings for the application.
   *
   * @return void
   */
  protected function registerCacheBindings()
  {
    $this->singleton('cache', fn () => $this->loadComponent('cache', CacheServiceProvider::class));
    $this->singleton('cache.store', fn () => $this->loadComponent('cache', CacheServiceProvider::class, 'cache.store'));
  }

  /**
   * Register container bindings for the application.
   *
   * @return void
   */
  protected function registerComposerBindings()
  {
    $this->singleton('composer', fn ($app): Composer => new Composer($app->make('files'), $this->basePath()));
  }

  /**
   * Register container bindings for the application.
   *
   * @return void
   */
  protected function registerConfigBindings()
  {
    $this->singleton('config', fn (): ConfigRepository => new ConfigRepository);
  }

  /**
   * Register container bindings for the application.
   *
   * @return void
   */
  protected function registerDatabaseBindings()
  {
    $this->singleton('db', function () {
      $this->configure('app');

      return $this->loadComponent(
        'database', [
          DatabaseServiceProvider::class,
          PaginationServiceProvider::class,
        ], 'db'
      );
    });
  }

  /**
   * Register container bindings for the application.
   *
   * @return void
   */
  protected function registerEncrypterBindings()
  {
    $this->singleton('encrypter', fn () => $this->loadComponent('app', EncryptionServiceProvider::class, 'encrypter'));
  }

  /**
   * Register container bindings for the application.
   *
   * @return void
   */
  protected function registerEventBindings()
  {
    $this->singleton('events', function () {
      $this->register(EventServiceProvider::class);

      return $this->make('events');
    });
  }

  /**
   * Register container bindings for the application.
   *
   * @return void
   */
  protected function registerFilesBindings()
  {
    $this->singleton('files', fn (): Filesystem => new Filesystem);
  }

  /**
   * Register container bindings for the application.
   *
   * @return void
   */
  protected function registerFilesystemBindings()
  {
    $this->singleton('filesystem', fn () => $this->loadComponent('filesystems', FilesystemServiceProvider::class, 'filesystem'));
    $this->singleton('filesystem.disk', fn () => $this->loadComponent('filesystems', FilesystemServiceProvider::class, 'filesystem.disk'));
    $this->singleton('filesystem.cloud', fn () => $this->loadComponent('filesystems', FilesystemServiceProvider::class, 'filesystem.cloud'));
  }

  /**
   * Register container bindings for the application.
   *
   * @return void
   */
  protected function registerHashBindings()
  {
    $this->singleton('hash', function () {
      $this->register(HashServiceProvider::class);

      return $this->make('hash');
    });
  }

  /**
   * Register container bindings for the application.
   *
   * @return void
   */
  protected function registerLogBindings()
  {
    $this->singleton(LoggerInterface::class, function (): LogManager {
      $this->configure('logging');

      return new LogManager($this);
    });
  }

  /**
   * Register container bindings for the application.
   *
   * @return void
   */
  protected function registerQueueBindings()
  {
    $this->singleton('queue', fn () => $this->loadComponent('queue', QueueServiceProvider::class, 'queue'));
    $this->singleton('queue.connection', fn () => $this->loadComponent('queue', QueueServiceProvider::class, 'queue.connection'));
  }

  /**
   * Register container bindings for the application.
   *
   * @return void
   */
  protected function registerRouterBindings()
  {
    $this->singleton('router', fn () => $this->router);
  }

  /**
   * Prepare the given request instance for use with the application.
   *
   * @return \Illuminate\Http\Request
   */
  protected function prepareRequest(SymfonyRequest $symfonyRequest)
  {
    if (! $symfonyRequest instanceof Request) {
      $symfonyRequest = Request::createFromBase($symfonyRequest);
    }

    $symfonyRequest->setUserResolver(fn ($guard = null) => $this->make('auth')->guard($guard)->user())->setRouteResolver(fn () => $this->currentRoute);

    return $symfonyRequest;
  }

  /**
   * Register container bindings for the PSR-7 request implementation.
   *
   * @return void
   */
  protected function registerPsrRequestBindings()
  {
    $this->singleton(ServerRequestInterface::class, function ($app) {
      if (class_exists(Psr17Factory::class) && class_exists(PsrHttpFactory::class)) {
        $psr17Factory = new Psr17Factory;

        return (new PsrHttpFactory($psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory))
          ->createRequest($app->make('request'));
      }

      throw new BindingResolutionException('Unable to resolve PSR request. Please install symfony/psr-http-message-bridge and nyholm/psr7.');
    });
  }

  /**
   * Register container bindings for the PSR-7 response implementation.
   *
   * @return void
   */
  protected function registerPsrResponseBindings()
  {
    $this->singleton(ResponseInterface::class, function (): PsrResponse {
      if (class_exists(PsrResponse::class)) {
        return new PsrResponse;
      }

      throw new BindingResolutionException('Unable to resolve PSR response. Please install nyholm/psr7.');
    });
  }

  /**
   * Register container bindings for the application.
   *
   * @return void
   */
  protected function registerTranslationBindings()
  {
    $this->singleton('translator', function () {
      $this->configure('app');

      $this->instance('path.lang', $this->getLanguagePath());

      $this->register(TranslationServiceProvider::class);

      return $this->make('translator');
    });
  }

  /**
   * Get the path to the application's language files.
   */
  protected function getLanguagePath(): string
  {
    if (is_dir($langPath = $this->basePath().'/resources/lang')) {
      return $langPath;
    }

    return __DIR__.'/../resources/lang';
  }

  /**
   * Register container bindings for the application.
   *
   * @return void
   */
  protected function registerUrlGeneratorBindings()
  {
    $this->singleton('url', fn (): UrlGenerator => new UrlGenerator($this));
  }

  /**
   * Register container bindings for the application.
   *
   * @return void
   */
  protected function registerValidatorBindings()
  {
    $this->singleton('validator', function () {
      $this->register(ValidationServiceProvider::class);

      return $this->make('validator');
    });
  }

  /**
   * Register container bindings for the application.
   *
   * @return void
   */
  protected function registerViewBindings()
  {
    $this->singleton('view', fn () => $this->loadComponent('view', ViewServiceProvider::class));
  }

  /**
   * Configure and load the given component and provider.
   *
   * @param  string  $config
   * @param  array|string  $providers
   * @param  string|null  $return
   * @return mixed
   */
  public function loadComponent($config, $providers, $return = null)
  {
    $this->configure($config);

    foreach ((array) $providers as $provider) {
      $this->register($provider);
    }

    return $this->make($return ?: $config);
  }

  /**
   * Load a configuration file into the application.
   *
   * @param  string  $name
   */
  public function configure($name): void
  {
    if (isset($this->loadedConfigurations[$name])) {
      return;
    }

    $this->loadedConfigurations[$name] = true;

    $path = $this->getConfigurationPath($name);

    if ($path) {
      $this->make('config')->set($name, require $path);
    }
  }

  /**
   * Get the path to the given configuration file.
   *
   * If no name is provided, then we'll return the path to the config folder.
   *
   * @param  string|null  $name
   * @return string
   */
  public function getConfigurationPath($name = null): ?string
  {
    if (! $name) {
      $appConfigDir = $this->basePath('config').'/';
      if (file_exists($appConfigDir)) {
        return $appConfigDir;
      }

      if (file_exists($path = __DIR__.'/../config/')) {
        return $path;
      }
    } else {
      $appConfigPath = $this->basePath('config').'/'.$name.'.php';
      if (file_exists($appConfigPath)) {
        return $appConfigPath;
      }

      if (file_exists($path = __DIR__.'/../config/'.$name.'.php')) {
        return $path;
      }
    }

    return null;
  }

  /**
   * Register the facades for the application.
   *
   * @param  bool  $aliases
   * @param  array  $userAliases
   */
  public function withFacades($aliases = true, $userAliases = []): void
  {
    Facade::setFacadeApplication($this);

    if ($aliases) {
      $this->withAliases($userAliases);
    }
  }

  /**
   * Register the aliases for the application.
   *
   * @param  array  $userAliases
   */
  public function withAliases($userAliases = []): void
  {
    $defaults = [
      Auth::class => 'Auth',
      Cache::class => 'Cache',
      DB::class => 'DB',
      Event::class => 'Event',
      \Illuminate\Support\Facades\Gate::class => 'Gate',
      Log::class => 'Log',
      Queue::class => 'Queue',
      Route::class => 'Route',
      Schema::class => 'Schema',
      Storage::class => 'Storage',
      URL::class => 'URL',
      Validator::class => 'Validator',
    ];

    if (! static::$aliasesRegistered) {
      static::$aliasesRegistered = true;

      $merged = array_merge($defaults, $userAliases);

      foreach ($merged as $original => $alias) {
        if (! class_exists($alias)) {
          class_alias($original, $alias);
        }
      }
    }
  }

  /**
   * Load the Eloquent library for the application.
   */
  public function withEloquent(): void
  {
    $this->make('db');
  }

  /**
   * Get the path to the application "app" directory.
   */
  public function path(): string
  {
    return $this->basePath.DIRECTORY_SEPARATOR.'app';
  }

  /**
   * Get the base path for the application.
   *
   * @param  string  $path
   * @return string
   */
  public function basePath(?string $path = '')
  {
    if ($this->basePath !== null) {
      return $this->basePath.($path ? '/'.$path : $path);
    }

    $this->basePath = $this->runningInConsole() ? getcwd() : realpath(getcwd().'/../');

    return $this->basePath($path);
  }

  /**
   * Get the path to the application configuration files.
   *
   * @param  string  $path
   */
  public function configPath(?string $path = ''): string
  {
    return $this->basePath.DIRECTORY_SEPARATOR.'config'.($path ? DIRECTORY_SEPARATOR.$path : $path);
  }

  /**
   * Get the path to the database directory.
   *
   * @param  string  $path
   */
  public function databasePath(?string $path = ''): string
  {
    return $this->basePath.DIRECTORY_SEPARATOR.'database'.($path ? DIRECTORY_SEPARATOR.$path : $path);
  }

  /**
   * Get the path to the language files.
   *
   * @param  string  $path
   */
  public function langPath(?string $path = ''): string
  {
    return $this->getLanguagePath().($path != '' ? DIRECTORY_SEPARATOR.$path : '');
  }

  /**
   * Get the storage path for the application.
   *
   * @param  string|null  $path
   * @return string
   */
  public function storagePath($path = '')
  {
    return ($this->storagePath ?: $this->basePath.DIRECTORY_SEPARATOR.'storage').($path ? DIRECTORY_SEPARATOR.$path : $path);
  }

  /**
   * Set the storage directory.
   *
   * @param  string  $path
   * @return $this
   */
  public function useStoragePath($path): static
  {
    $this->storagePath = $path;

    $this->instance('path.storage', $path);

    return $this;
  }

  /**
   * Get the path to the resources directory.
   *
   * @param  string|null  $path
   * @return string
   */
  public function resourcePath($path = '')
  {
    return $this->basePath.DIRECTORY_SEPARATOR.'resources'.($path ? DIRECTORY_SEPARATOR.$path : $path);
  }

  /**
   * Determine if the application events are cached.
   */
  public function eventsAreCached(): bool
  {
    return false;
  }

  /**
   * Determine if the application is running in the console.
   */
  public function runningInConsole(): bool
  {
    return \PHP_SAPI === 'cli' || \PHP_SAPI === 'phpdbg';
  }

  /**
   * Determine if we are running unit tests.
   */
  public function runningUnitTests(): bool
  {
    return $this->environment() === 'testing';
  }

  /**
   * Prepare the application to execute a console command.
   *
   * @param  bool  $aliases
   */
  public function prepareForConsoleCommand($aliases = true): void
  {
    $this->withFacades($aliases);

    $this->make('cache');
    $this->make('queue');

    $this->configure('database');

    $this->register(MigrationServiceProvider::class);
    $this->register(ConsoleServiceProvider::class);
  }

  /**
   * Get the application namespace.
   *
   * @return string
   *
   * @throws RuntimeException
   */
  public function getNamespace()
  {
    if (! is_null($this->namespace)) {
      return $this->namespace;
    }

    $composer = json_decode(file_get_contents(base_path('composer.json')), true);

    foreach ((array) data_get($composer, 'autoload.psr-4') as $namespace => $path) {
      foreach ((array) $path as $pathChoice) {
        if (realpath(app()->path()) === realpath(base_path().'/'.$pathChoice)) {
          return $this->namespace = $namespace;
        }
      }
    }

    throw new RuntimeException('Unable to detect application namespace.');
  }

  /**
   * Flush the container of all bindings and resolved instances.
   */
  public function flush(): void
  {
    parent::flush();

    $this->middleware = [];
    $this->currentRoute = [];
    $this->loadedProviders = [];
    $this->routeMiddleware = [];
    $this->reboundCallbacks = [];
    $this->resolvingCallbacks = [];
    $this->availableBindings = [];
    $this->ranServiceBinders = [];
    $this->loadedConfigurations = [];
    $this->afterResolvingCallbacks = [];

    $this->router = null;
    $this->dispatcher = null;
    static::$instance = null;
    static::$aliasesRegistered = false;
  }

  /**
   * Get the current application locale.
   *
   * @return string
   */
  public function getLocale()
  {
    return $this['config']->get('app.locale');
  }

  /**
   * Get the current application fallback locale.
   *
   * @return string
   */
  public function getFallbackLocale()
  {
    return $this['config']->get('app.fallback_locale');
  }

  /**
   * Set the current application locale.
   *
   * @param  string  $locale
   */
  public function setLocale($locale): void
  {
    $this['config']->set('app.locale', $locale);
    $this['translator']->setLocale($locale);
  }

  /**
   * Determine if application locale is the given locale.
   *
   * @param  string  $locale
   */
  public function isLocale($locale): bool
  {
    return $this->getLocale() == $locale;
  }

  /**
   * Register a terminating callback with the application.
   *
   * @param  callable|string  $callback
   * @return $this
   */
  public function terminating($callback): static
  {
    $this->terminatingCallbacks[] = $callback;

    return $this;
  }

  /**
   * Terminate the application.
   */
  public function terminate(): void
  {
    $index = 0;

    while ($index < count($this->terminatingCallbacks)) {
      $this->call($this->terminatingCallbacks[$index]);

      $index++;
    }
  }

  /**
   * Register the core container aliases.
   *
   * @return void
   */
  protected function registerContainerAliases()
  {
    $this->aliases = [
      \Illuminate\Contracts\Foundation\Application::class => 'app',
      \Illuminate\Contracts\Auth\Factory::class => 'auth',
      Guard::class => 'auth.driver',
      \Illuminate\Contracts\Cache\Factory::class => 'cache',
      \Illuminate\Contracts\Cache\Repository::class => 'cache.store',
      \Illuminate\Contracts\Config\Repository::class => 'config',
      ConfigRepository::class => 'config',
      Container::class => 'app',
      \Illuminate\Contracts\Container\Container::class => 'app',
      ConnectionResolverInterface::class => 'db',
      DatabaseManager::class => 'db',
      Encrypter::class => 'encrypter',
      \Illuminate\Contracts\Events\Dispatcher::class => 'events',
      \Illuminate\Contracts\Filesystem\Factory::class => 'filesystem',
      \Illuminate\Contracts\Filesystem\Filesystem::class => 'filesystem.disk',
      Cloud::class => 'filesystem.cloud',
      Hasher::class => 'hash',
      'log' => LoggerInterface::class,
      \Illuminate\Contracts\Queue\Factory::class => 'queue',
      \Illuminate\Contracts\Queue\Queue::class => 'queue.connection',
      RedisManager::class => 'redis',
      \Illuminate\Contracts\Redis\Factory::class => 'redis',
      Connection::class => 'redis.connection',
      \Illuminate\Contracts\Redis\Connection::class => 'redis.connection',
      'request' => Request::class,
      Router::class => 'router',
      Translator::class => 'translator',
      UrlGenerator::class => 'url',
      \Illuminate\Contracts\Validation\Factory::class => 'validator',
      \Illuminate\Contracts\View\Factory::class => 'view',
    ];
  }

  /**
   * The available container bindings and their respective load methods.
   *
   * @var array
   */
  public $availableBindings = [
    'auth' => 'registerAuthBindings',
    'auth.driver' => 'registerAuthBindings',
    AuthManager::class => 'registerAuthBindings',
    Guard::class => 'registerAuthBindings',
    Gate::class => 'registerAuthBindings',
    Broadcaster::class => 'registerBroadcastingBindings',
    Factory::class => 'registerBroadcastingBindings',
    Dispatcher::class => 'registerBusBindings',
    'cache' => 'registerCacheBindings',
    'cache.store' => 'registerCacheBindings',
    \Illuminate\Contracts\Cache\Factory::class => 'registerCacheBindings',
    \Illuminate\Contracts\Cache\Repository::class => 'registerCacheBindings',
    'composer' => 'registerComposerBindings',
    'config' => 'registerConfigBindings',
    'db' => 'registerDatabaseBindings',
    \Illuminate\Database\Eloquent\Factory::class => 'registerDatabaseBindings',
    'filesystem' => 'registerFilesystemBindings',
    'filesystem.cloud' => 'registerFilesystemBindings',
    'filesystem.disk' => 'registerFilesystemBindings',
    Cloud::class => 'registerFilesystemBindings',
    \Illuminate\Contracts\Filesystem\Filesystem::class => 'registerFilesystemBindings',
    \Illuminate\Contracts\Filesystem\Factory::class => 'registerFilesystemBindings',
    'encrypter' => 'registerEncrypterBindings',
    Encrypter::class => 'registerEncrypterBindings',
    'events' => 'registerEventBindings',
    \Illuminate\Contracts\Events\Dispatcher::class => 'registerEventBindings',
    'files' => 'registerFilesBindings',
    'hash' => 'registerHashBindings',
    Hasher::class => 'registerHashBindings',
    'log' => 'registerLogBindings',
    LoggerInterface::class => 'registerLogBindings',
    'queue' => 'registerQueueBindings',
    'queue.connection' => 'registerQueueBindings',
    \Illuminate\Contracts\Queue\Factory::class => 'registerQueueBindings',
    \Illuminate\Contracts\Queue\Queue::class => 'registerQueueBindings',
    'router' => 'registerRouterBindings',
    ServerRequestInterface::class => 'registerPsrRequestBindings',
    ResponseInterface::class => 'registerPsrResponseBindings',
    'translator' => 'registerTranslationBindings',
    'url' => 'registerUrlGeneratorBindings',
    'validator' => 'registerValidatorBindings',
    \Illuminate\Contracts\Validation\Factory::class => 'registerValidatorBindings',
    'view' => 'registerViewBindings',
    \Illuminate\Contracts\View\Factory::class => 'registerViewBindings',
  ];
}
