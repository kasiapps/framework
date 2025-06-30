<?php

namespace Kasi\Foundation;

use Kasi\Auth\AuthManager;
use Kasi\Auth\AuthServiceProvider;
use Kasi\Broadcasting\BroadcastServiceProvider;
use Kasi\Bus\BusServiceProvider;
use Kasi\Cache\CacheServiceProvider;
use Kasi\Config\Repository as ConfigRepository;
use Kasi\Container\Container;
use Kasi\Contracts\Auth\Access\Gate;
use Kasi\Contracts\Broadcasting\Broadcaster;
use Kasi\Contracts\Broadcasting\Factory;
use Kasi\Contracts\Bus\Dispatcher;
use Kasi\Contracts\Container\BindingResolutionException;
use Kasi\Database\DatabaseServiceProvider;
use Kasi\Database\MigrationServiceProvider;
use Kasi\Encryption\EncryptionServiceProvider;
use Kasi\Events\EventServiceProvider;
use Kasi\Filesystem\Filesystem;
use Kasi\Filesystem\FilesystemServiceProvider;
use Kasi\Hashing\HashServiceProvider;
use Kasi\Http\Request;
use Kasi\Log\LogManager;
use Kasi\Pagination\PaginationServiceProvider;
use Kasi\Queue\QueueServiceProvider;
use Kasi\Support\Composer;
use Kasi\Support\Facades\Facade;
use Kasi\Support\ServiceProvider;
use Kasi\Support\Str;
use Kasi\Translation\TranslationServiceProvider;
use Kasi\Validation\ValidationServiceProvider;
use Kasi\View\ViewServiceProvider;
use Kasi\Foundation\Console\ConsoleServiceProvider;
use Kasi\Foundation\Routing\Router;
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
  use Concerns\RegistersExceptionHandlers,
    Concerns\RoutesRequests;

  /**
   * Indicates if the class aliases have been registered.
   *
   * @var bool
   */
  protected static $aliasesRegistered = false;

  /**
   * The base path of the application installation.
   *
   * @var string
   */
  protected $basePath;

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
   * @var \Kasi\Foundation\Routing\Router
   */
  public $router;

  /**
   * The array of terminating callbacks.
   *
   * @var callable[]
   */
  protected $terminatingCallbacks = [];

  /**
   * Create a new Kasi application instance.
   *
   * @param  string|null  $basePath
   * @return void
   */
  public function __construct($basePath = null)
  {
    $this->basePath = $basePath;

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
   *
   * @return void
   */
  public function bootstrapRouter()
  {
    $this->router = new Router($this);
  }

  /**
   * Get the version number of the application.
   *
   * @return string
   */
  public function version()
  {
    return 'Kasi (11.1.0) (Kasi Components ^11.0)';
  }

  /**
   * Determine if the application is currently down for maintenance.
   *
   * @return bool
   */
  public function isDownForMaintenance()
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
   *
   * @return bool
   */
  public function isLocal()
  {
    return $this->environment() === 'local';
  }

  /**
   * Determine if the application is in the production environment.
   *
   * @return bool
   */
  public function isProduction()
  {
    return $this->environment() === 'production';
  }

  /**
   * Determine if the given service provider is loaded.
   *
   * @param  string  $provider
   * @return bool
   */
  public function providerIsLoaded(string $provider)
  {
    return isset($this->loadedProviders[$provider]);
  }

  /**
   * Register a service provider with the application.
   *
   * @param  \Kasi\Support\ServiceProvider|string  $provider
   * @return void
   */
  public function register($provider)
  {
    if (! $provider instanceof ServiceProvider) {
      $provider = new $provider($this);
    }

    if (array_key_exists($providerName = get_class($provider), $this->loadedProviders)) {
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
   * @return void
   */
  public function registerDeferredProvider($provider)
  {
    $this->register($provider);
  }

  /**
   * Boots the registered providers.
   */
  public function boot()
  {
    if ($this->booted) {
      return;
    }

    foreach ($this->loadedProviders as $provider) {
      $this->bootProvider($provider);
    }

    $this->booted = true;
  }

  /**
   * Boot the given service provider.
   *
   * @param  \Kasi\Support\ServiceProvider  $provider
   * @return mixed
   */
  protected function bootProvider(ServiceProvider $provider)
  {
    if (method_exists($provider, 'boot')) {
      return $this->call([$provider, 'boot']);
    }
  }

  /**
   * Resolve the given type from the container.
   *
   * @param  string  $abstract
   * @param  array  $parameters
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
    $this->singleton('auth', function () {
      return $this->loadComponent('auth', AuthServiceProvider::class, 'auth');
    });

    $this->singleton('auth.driver', function () {
      return $this->loadComponent('auth', AuthServiceProvider::class, 'auth.driver');
    });

    $this->singleton(AuthManager::class, function () {
      return $this->loadComponent('auth', AuthServiceProvider::class, 'auth');
    });

    $this->singleton(Gate::class, function () {
      return $this->loadComponent('auth', AuthServiceProvider::class, Gate::class);
    });
  }

  /**
   * Register container bindings for the application.
   *
   * @return void
   */
  protected function registerBroadcastingBindings()
  {
    $this->singleton(Factory::class, function () {
      return $this->loadComponent('broadcasting', BroadcastServiceProvider::class, Factory::class);
    });

    $this->singleton(Broadcaster::class, function () {
      return $this->loadComponent('broadcasting', BroadcastServiceProvider::class, Broadcaster::class);
    });
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
    $this->singleton('cache', function () {
      return $this->loadComponent('cache', CacheServiceProvider::class);
    });
    $this->singleton('cache.store', function () {
      return $this->loadComponent('cache', CacheServiceProvider::class, 'cache.store');
    });
  }

  /**
   * Register container bindings for the application.
   *
   * @return void
   */
  protected function registerComposerBindings()
  {
    $this->singleton('composer', function ($app) {
      return new Composer($app->make('files'), $this->basePath());
    });
  }

  /**
   * Register container bindings for the application.
   *
   * @return void
   */
  protected function registerConfigBindings()
  {
    $this->singleton('config', function () {
      return new ConfigRepository;
    });
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
    $this->singleton('encrypter', function () {
      return $this->loadComponent('app', EncryptionServiceProvider::class, 'encrypter');
    });
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
    $this->singleton('files', function () {
      return new Filesystem;
    });
  }

  /**
   * Register container bindings for the application.
   *
   * @return void
   */
  protected function registerFilesystemBindings()
  {
    $this->singleton('filesystem', function () {
      return $this->loadComponent('filesystems', FilesystemServiceProvider::class, 'filesystem');
    });
    $this->singleton('filesystem.disk', function () {
      return $this->loadComponent('filesystems', FilesystemServiceProvider::class, 'filesystem.disk');
    });
    $this->singleton('filesystem.cloud', function () {
      return $this->loadComponent('filesystems', FilesystemServiceProvider::class, 'filesystem.cloud');
    });
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
    $this->singleton(LoggerInterface::class, function () {
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
    $this->singleton('queue', function () {
      return $this->loadComponent('queue', QueueServiceProvider::class, 'queue');
    });
    $this->singleton('queue.connection', function () {
      return $this->loadComponent('queue', QueueServiceProvider::class, 'queue.connection');
    });
  }

  /**
   * Register container bindings for the application.
   *
   * @return void
   */
  protected function registerRouterBindings()
  {
    $this->singleton('router', function () {
      return $this->router;
    });
  }

  /**
   * Prepare the given request instance for use with the application.
   *
   * @param  \Symfony\Component\HttpFoundation\Request  $request
   * @return \Kasi\Http\Request
   */
  protected function prepareRequest(SymfonyRequest $request)
  {
    if (! $request instanceof Request) {
      $request = Request::createFromBase($request);
    }

    $request->setUserResolver(function ($guard = null) {
      return $this->make('auth')->guard($guard)->user();
    })->setRouteResolver(function () {
      return $this->currentRoute;
    });

    return $request;
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
    $this->singleton(ResponseInterface::class, function () {
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
   *
   * @return string
   */
  protected function getLanguagePath()
  {
    if (is_dir($langPath = $this->basePath().'/resources/lang')) {
      return $langPath;
    } else {
      return __DIR__.'/../resources/lang';
    }
  }

  /**
   * Register container bindings for the application.
   *
   * @return void
   */
  protected function registerUrlGeneratorBindings()
  {
    $this->singleton('url', function () {
      return new Routing\UrlGenerator($this);
    });
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
    $this->singleton('view', function () {
      return $this->loadComponent('view', ViewServiceProvider::class);
    });
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
   * @return void
   */
  public function configure($name)
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
  public function getConfigurationPath($name = null)
  {
    if (! $name) {
      $appConfigDir = $this->basePath('config').'/';

      if (file_exists($appConfigDir)) {
        return $appConfigDir;
      } elseif (file_exists($path = __DIR__.'/../config/')) {
        return $path;
      }
    } else {
      $appConfigPath = $this->basePath('config').'/'.$name.'.php';

      if (file_exists($appConfigPath)) {
        return $appConfigPath;
      } elseif (file_exists($path = __DIR__.'/../config/'.$name.'.php')) {
        return $path;
      }
    }
  }

  /**
   * Register the facades for the application.
   *
   * @param  bool  $aliases
   * @param  array  $userAliases
   * @return void
   */
  public function withFacades($aliases = true, $userAliases = [])
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
   * @return void
   */
  public function withAliases($userAliases = [])
  {
    $defaults = [
      \Kasi\Support\Facades\Auth::class => 'Auth',
      \Kasi\Support\Facades\Cache::class => 'Cache',
      \Kasi\Support\Facades\DB::class => 'DB',
      \Kasi\Support\Facades\Event::class => 'Event',
      \Kasi\Support\Facades\Gate::class => 'Gate',
      \Kasi\Support\Facades\Log::class => 'Log',
      \Kasi\Support\Facades\Queue::class => 'Queue',
      \Kasi\Support\Facades\Route::class => 'Route',
      \Kasi\Support\Facades\Schema::class => 'Schema',
      \Kasi\Support\Facades\Storage::class => 'Storage',
      \Kasi\Support\Facades\URL::class => 'URL',
      \Kasi\Support\Facades\Validator::class => 'Validator',
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
   *
   * @return void
   */
  public function withEloquent()
  {
    $this->make('db');
  }

  /**
   * Get the path to the application "app" directory.
   *
   * @return string
   */
  public function path()
  {
    return $this->basePath.DIRECTORY_SEPARATOR.'app';
  }

  /**
   * Get the base path for the application.
   *
   * @param  string  $path
   * @return string
   */
  public function basePath($path = '')
  {
    if (isset($this->basePath)) {
      return $this->basePath.($path ? '/'.$path : $path);
    }

    if ($this->runningInConsole()) {
      $this->basePath = getcwd();
    } else {
      $this->basePath = realpath(getcwd().'/../');
    }

    return $this->basePath($path);
  }

  /**
   * Get the path to the application configuration files.
   *
   * @param  string  $path
   * @return string
   */
  public function configPath($path = '')
  {
    return $this->basePath.DIRECTORY_SEPARATOR.'config'.($path ? DIRECTORY_SEPARATOR.$path : $path);
  }

  /**
   * Get the path to the database directory.
   *
   * @param  string  $path
   * @return string
   */
  public function databasePath($path = '')
  {
    return $this->basePath.DIRECTORY_SEPARATOR.'database'.($path ? DIRECTORY_SEPARATOR.$path : $path);
  }

  /**
   * Get the path to the language files.
   *
   * @param  string  $path
   * @return string
   */
  public function langPath($path = '')
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
  public function useStoragePath($path)
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
   *
   * @return bool
   */
  public function eventsAreCached()
  {
    return false;
  }

  /**
   * Determine if the application is running in the console.
   *
   * @return bool
   */
  public function runningInConsole()
  {
    return \PHP_SAPI === 'cli' || \PHP_SAPI === 'phpdbg';
  }

  /**
   * Determine if we are running unit tests.
   *
   * @return bool
   */
  public function runningUnitTests()
  {
    return $this->environment() === 'testing';
  }

  /**
   * Prepare the application to execute a console command.
   *
   * @param  bool  $aliases
   * @return void
   */
  public function prepareForConsoleCommand($aliases = true)
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
   * @throws \RuntimeException
   */
  public function getNamespace()
  {
    if (! is_null($this->namespace)) {
      return $this->namespace;
    }

    $composer = json_decode(file_get_contents(base_path('composer.json')), true);

    foreach ((array) data_get($composer, 'autoload.psr-4') as $namespace => $path) {
      foreach ((array) $path as $pathChoice) {
        if (realpath(app()->path()) == realpath(base_path().'/'.$pathChoice)) {
          return $this->namespace = $namespace;
        }
      }
    }

    throw new RuntimeException('Unable to detect application namespace.');
  }

  /**
   * Flush the container of all bindings and resolved instances.
   *
   * @return void
   */
  public function flush()
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
   * @return void
   */
  public function setLocale($locale)
  {
    $this['config']->set('app.locale', $locale);
    $this['translator']->setLocale($locale);
  }

  /**
   * Determine if application locale is the given locale.
   *
   * @param  string  $locale
   * @return bool
   */
  public function isLocale($locale)
  {
    return $this->getLocale() == $locale;
  }

  /**
   * Register a terminating callback with the application.
   *
   * @param  callable|string  $callback
   * @return $this
   */
  public function terminating($callback)
  {
    $this->terminatingCallbacks[] = $callback;

    return $this;
  }

  /**
   * Terminate the application.
   *
   * @return void
   */
  public function terminate()
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
      \Kasi\Contracts\Foundation\Application::class => 'app',
      \Kasi\Contracts\Auth\Factory::class => 'auth',
      \Kasi\Contracts\Auth\Guard::class => 'auth.driver',
      \Kasi\Contracts\Cache\Factory::class => 'cache',
      \Kasi\Contracts\Cache\Repository::class => 'cache.store',
      \Kasi\Contracts\Config\Repository::class => 'config',
      \Kasi\Config\Repository::class => 'config',
      \Kasi\Container\Container::class => 'app',
      \Kasi\Contracts\Container\Container::class => 'app',
      \Kasi\Database\ConnectionResolverInterface::class => 'db',
      \Kasi\Database\DatabaseManager::class => 'db',
      \Kasi\Contracts\Encryption\Encrypter::class => 'encrypter',
      \Kasi\Contracts\Events\Dispatcher::class => 'events',
      \Kasi\Contracts\Filesystem\Factory::class => 'filesystem',
      \Kasi\Contracts\Filesystem\Filesystem::class => 'filesystem.disk',
      \Kasi\Contracts\Filesystem\Cloud::class => 'filesystem.cloud',
      \Kasi\Contracts\Hashing\Hasher::class => 'hash',
      'log' => \Psr\Log\LoggerInterface::class,
      \Kasi\Contracts\Queue\Factory::class => 'queue',
      \Kasi\Contracts\Queue\Queue::class => 'queue.connection',
      \Kasi\Redis\RedisManager::class => 'redis',
      \Kasi\Contracts\Redis\Factory::class => 'redis',
      \Kasi\Redis\Connections\Connection::class => 'redis.connection',
      \Kasi\Contracts\Redis\Connection::class => 'redis.connection',
      'request' => \Kasi\Http\Request::class,
      \Kasi\Foundation\Routing\Router::class => 'router',
      \Kasi\Contracts\Translation\Translator::class => 'translator',
      \Kasi\Foundation\Routing\UrlGenerator::class => 'url',
      \Kasi\Contracts\Validation\Factory::class => 'validator',
      \Kasi\Contracts\View\Factory::class => 'view',
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
    \Kasi\Auth\AuthManager::class => 'registerAuthBindings',
    \Kasi\Contracts\Auth\Guard::class => 'registerAuthBindings',
    \Kasi\Contracts\Auth\Access\Gate::class => 'registerAuthBindings',
    \Kasi\Contracts\Broadcasting\Broadcaster::class => 'registerBroadcastingBindings',
    \Kasi\Contracts\Broadcasting\Factory::class => 'registerBroadcastingBindings',
    \Kasi\Contracts\Bus\Dispatcher::class => 'registerBusBindings',
    'cache' => 'registerCacheBindings',
    'cache.store' => 'registerCacheBindings',
    \Kasi\Contracts\Cache\Factory::class => 'registerCacheBindings',
    \Kasi\Contracts\Cache\Repository::class => 'registerCacheBindings',
    'composer' => 'registerComposerBindings',
    'config' => 'registerConfigBindings',
    'db' => 'registerDatabaseBindings',
    \Kasi\Database\Eloquent\Factory::class => 'registerDatabaseBindings',
    'filesystem' => 'registerFilesystemBindings',
    'filesystem.cloud' => 'registerFilesystemBindings',
    'filesystem.disk' => 'registerFilesystemBindings',
    \Kasi\Contracts\Filesystem\Cloud::class => 'registerFilesystemBindings',
    \Kasi\Contracts\Filesystem\Filesystem::class => 'registerFilesystemBindings',
    \Kasi\Contracts\Filesystem\Factory::class => 'registerFilesystemBindings',
    'encrypter' => 'registerEncrypterBindings',
    \Kasi\Contracts\Encryption\Encrypter::class => 'registerEncrypterBindings',
    'events' => 'registerEventBindings',
    \Kasi\Contracts\Events\Dispatcher::class => 'registerEventBindings',
    'files' => 'registerFilesBindings',
    'hash' => 'registerHashBindings',
    \Kasi\Contracts\Hashing\Hasher::class => 'registerHashBindings',
    'log' => 'registerLogBindings',
    \Psr\Log\LoggerInterface::class => 'registerLogBindings',
    'queue' => 'registerQueueBindings',
    'queue.connection' => 'registerQueueBindings',
    \Kasi\Contracts\Queue\Factory::class => 'registerQueueBindings',
    \Kasi\Contracts\Queue\Queue::class => 'registerQueueBindings',
    'router' => 'registerRouterBindings',
    \Psr\Http\Message\ServerRequestInterface::class => 'registerPsrRequestBindings',
    \Psr\Http\Message\ResponseInterface::class => 'registerPsrResponseBindings',
    'translator' => 'registerTranslationBindings',
    'url' => 'registerUrlGeneratorBindings',
    'validator' => 'registerValidatorBindings',
    \Kasi\Contracts\Validation\Factory::class => 'registerValidatorBindings',
    'view' => 'registerViewBindings',
    \Kasi\Contracts\View\Factory::class => 'registerViewBindings',
  ];
}
