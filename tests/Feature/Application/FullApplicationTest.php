<?php

declare(strict_types=1);

use FastRoute\DataGenerator\GroupCountBased;
use FastRoute\RouteCollector;
use FastRoute\RouteParser\Std;
use Illuminate\Auth\GenericUser;
use Illuminate\Bus\Dispatcher;
use Illuminate\Console\Command;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Contracts\Validation\Factory;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\ViewServiceProvider;
use Laravel\Lumen\Application;
use Laravel\Lumen\Console\ConsoleServiceProvider;
use Laravel\Lumen\Console\Kernel;
use Laravel\Lumen\Http\Request;
use Laravel\Lumen\Routing\Controller;
use Mockery as m;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

beforeEach(function () {
  Facade::clearResolvedInstances();
});

afterEach(function () {
  m::close();

  // Restore error handlers to prevent warnings
  restore_error_handler();
  restore_exception_handler();

  // Reset the static aliases registered flag to allow fresh alias registration in each test
  $reflection = new \ReflectionClass(Application::class);
  $property = $reflection->getProperty('aliasesRegistered');
  $property->setAccessible(true);
  $property->setValue(null, false);
});

it('basic request', function () {
  $application = new Application;

  $application->router->get('/', fn () => response('Hello World'));

  $response = $application->handle($request = Request::create('/', 'GET'));

  expect($response->getStatusCode())->toBe(200);
  expect($response->getContent())->toBe('Hello World');
  expect($request)->toBeInstanceOf(Request::class);
});

it('basic symfony request', function () {
  $application = new Application;

  $application->router->get('/', fn () => response('Hello World'));

  $response = $application->handle(SymfonyRequest::create('/', 'GET'));
  expect($response->getStatusCode())->toBe(200);
});

it('add route multiple method request', function () {
  $application = new Application;

  $application->router->addRoute(['GET', 'POST'], '/', fn () => response('Hello World'));

  $response = $application->handle(Request::create('/', 'GET'));

  expect($response->getStatusCode())->toBe(200);
  expect($response->getContent())->toBe('Hello World');

  $response = $application->handle(Request::create('/', 'POST'));

  expect($response->getStatusCode())->toBe(200);
  expect($response->getContent())->toBe('Hello World');
});

it('request with parameters', function () {
  $application = new Application;

  $application->router->get('/foo/{bar}/{baz}', fn ($bar, $baz) => response($bar.$baz));

  $response = $application->handle($request = Request::create('/foo/1/2', 'GET'));

  expect($response->getStatusCode())->toBe(200);
  expect($response->getContent())->toBe('12');
  expect($request->route('bar'))->toBe('1'); // Route parameters are strings
  expect($request->route('baz'))->toBe('2'); // Route parameters are strings
});

it('callback route with default parameter', function () {
  $application = new Application;
  $application->router->get('/foo-bar/{baz}', fn ($baz = 'default-value') => response($baz));

  $response = $application->handle(Request::create('/foo-bar/something', 'GET'));

  expect($response->getStatusCode())->toBe(200);
  expect($response->getContent())->toBe('something');
});

it('global middleware', function () {
  $application = new Application;

  $application->middleware(['LumenTestMiddleware']);

  $application->router->get('/', fn () => response('Hello World'));

  $response = $application->handle(Request::create('/', 'GET'));

  expect($response->getStatusCode())->toBe(200);
  expect($response->getContent())->toBe('Middleware');
});

it('route middleware', function () {
  $application = new Application;

  $application->routeMiddleware(['foo' => 'LumenTestMiddleware', 'passing' => 'LumenTestPlainMiddleware']);

  $application->router->get('/', fn () => response('Hello World'));

  $application->router->get('/foo', ['middleware' => 'foo', fn () => response('Hello World')]);

  $application->router->get('/bar', ['middleware' => ['foo'], fn () => response('Hello World')]);

  $application->router->get('/fooBar', ['middleware' => 'passing|foo', fn () => response('Hello World')]);

  $response = $application->handle(Request::create('/', 'GET'));
  expect($response->getStatusCode())->toBe(200);
  expect($response->getContent())->toBe('Hello World');

  $response = $application->handle(Request::create('/foo', 'GET'));
  expect($response->getStatusCode())->toBe(200);
  expect($response->getContent())->toBe('Middleware');

  $response = $application->handle(Request::create('/bar', 'GET'));
  expect($response->getStatusCode())->toBe(200);
  expect($response->getContent())->toBe('Middleware');

  $response = $application->handle(Request::create('/fooBar', 'GET'));
  expect($response->getStatusCode())->toBe(200);
  expect($response->getContent())->toBe('Middleware');
});

it('global middleware parameters', function () {
  $application = new Application;

  $application->middleware(['LumenTestParameterizedMiddleware:foo,bar']);

  $application->router->get('/', fn () => response('Hello World'));

  $response = $application->handle(Request::create('/', 'GET'));

  expect($response->getStatusCode())->toBe(200);
  expect($response->getContent())->toBe('Middleware - foo - bar');
});

it('route middleware parameters', function () {
  $application = new Application;

  $application->routeMiddleware(['foo' => 'LumenTestParameterizedMiddleware', 'passing' => 'LumenTestPlainMiddleware']);

  $application->router->get('/', ['middleware' => 'passing|foo:bar,boom', fn () => response('Hello World')]);

  $response = $application->handle(Request::create('/', 'GET'));

  expect($response->getStatusCode())->toBe(200);
  expect($response->getContent())->toBe('Middleware - bar - boom');
});

it('with middleware disabled', function () {
  $application = new Application;

  $application->middleware(['LumenTestMiddleware']);
  $application->instance('middleware.disable', true);

  $application->router->get('/', fn () => response('Hello World'));

  $response = $application->handle(Request::create('/', 'GET'));

  expect($response->getStatusCode())->toBe(200);
  expect($response->getContent())->toBe('Hello World');
});

it('terminable global middleware', function () {
  $application = new Application;

  $application->middleware(['LumenTestTerminateMiddleware']);

  $application->router->get('/', fn () => response('Hello World'));

  $response = $application->handle(Request::create('/', 'GET'));

  expect($response->getStatusCode())->toBe(200);
  expect($response->getContent())->toBe('TERMINATED');
});

it('terminate with middleware disabled', function () {
  $application = new Application;

  $application->middleware(['LumenTestTerminateMiddleware']);
  $application->instance('middleware.disable', true);

  $application->router->get('/', fn () => response('Hello World'));

  $response = $application->handle(Request::create('/', 'GET'));

  expect($response->getStatusCode())->toBe(200);
  expect($response->getContent())->toBe('Hello World');
});

it('not found response', function () {
  $application = new Application;
  $application->instance(ExceptionHandler::class, $mock = m::mock('Laravel\Lumen\Exceptions\Handler[report]'));
  $mock->shouldIgnoreMissing();

  $application->router->get('/', fn () => response('Hello World'));

  $response = $application->handle(Request::create('/foo', 'GET'));

  expect($response->getStatusCode())->toBe(404);
});

it('method not allowed response', function () {
  $application = new Application;
  $application->instance(ExceptionHandler::class, $mock = m::mock('Laravel\Lumen\Exceptions\Handler[report]'));
  $mock->shouldIgnoreMissing();

  $application->router->post('/', fn () => response('Hello World'));

  $response = $application->handle(Request::create('/', 'GET'));

  expect($response->getStatusCode())->toBe(405);
});

it('responsable interface', function () {
  $application = new Application;

  $application->router->get('/foo/{foo}', fn (): \ResponsableResponse => new ResponsableResponse);

  $request = Request::create('/foo/999', 'GET');
  $response = $application->handle($request);

  expect($request->route('foo'))->toBe('999'); // Route parameters are strings
  expect($response->original)->toBe('999'); // Response original is also string
});

it('uncaught exception response', function () {
  $application = new Application;
  $application->instance(ExceptionHandler::class, $mock = m::mock('Laravel\Lumen\Exceptions\Handler[report]'));
  $mock->shouldIgnoreMissing();

  $application->router->get('/', function (): void {
    throw new RuntimeException('app exception');
  });

  $response = $application->handle(Request::create('/', 'GET'));
  expect($response)->toBeInstanceOf(Response::class);
});

it('generating urls', function () {
  $application = new Application;
  $application->instance('request', Request::create('http://lumen.laravel.com', 'GET'));

  $application->router->get('/foo-bar', ['as' => 'foo', function (): void {
    //
  }]);

  $application->router->get('/foo-bar/{baz}/{boom}', ['as' => 'bar', function (): void {
    //
  }]);

  $application->router->get('/foo-bar/{baz}[/{boom}]', ['as' => 'optional', function (): void {
    //
  }]);

  $application->router->get('/foo-bar/{baz:[0-9]+}[/{boom}]', ['as' => 'regex', function (): void {
    //
  }]);

  expect(url('something'))->toBe('http://lumen.laravel.com/something');
  expect(route('foo'))->toBe('http://lumen.laravel.com/foo-bar');
  expect(route('bar', ['baz' => 1, 'boom' => 2]))->toBe('http://lumen.laravel.com/foo-bar/1/2');
  expect(route('foo', ['baz' => 1, 'boom' => 2]))->toBe('http://lumen.laravel.com/foo-bar?baz=1&boom=2');
  expect(route('optional', ['baz' => 1, 'boom' => 2]))->toBe('http://lumen.laravel.com/foo-bar/1/2');
  expect(route('optional', ['baz' => 1]))->toBe('http://lumen.laravel.com/foo-bar/1');
  expect(route('regex', ['baz' => 1, 'boom' => 2]))->toBe('http://lumen.laravel.com/foo-bar/1/2');
  expect(route('regex', ['baz' => 1]))->toBe('http://lumen.laravel.com/foo-bar/1');
});

it('generating urls for regex parameters', function () {
  $application = new Application;
  $application->instance('request', Request::create('http://lumen.laravel.com', 'GET'));

  $application->router->get('/foo-bar', ['as' => 'foo', function (): void {
    //
  }]);

  $application->router->get('/foo-bar/{baz:[0-9]+}/{boom}', ['as' => 'bar', function (): void {
    //
  }]);

  $application->router->get('/foo-bar/{baz:[0-9]+}/{boom:[0-9]+}', ['as' => 'baz', function (): void {
    //
  }]);

  $application->router->get('/foo-bar/{baz:[0-9]{2,5}}', ['as' => 'boom', function (): void {
    //
  }]);

  expect(url('something'))->toBe('http://lumen.laravel.com/something');
  expect(route('foo'))->toBe('http://lumen.laravel.com/foo-bar');
  expect(route('bar', ['baz' => 1, 'boom' => 2]))->toBe('http://lumen.laravel.com/foo-bar/1/2');
  expect(route('baz', ['baz' => 1, 'boom' => 2]))->toBe('http://lumen.laravel.com/foo-bar/1/2');
  expect(route('baz', ['ba' => 1, 'bo' => 2]))->toBe('http://lumen.laravel.com/foo-bar/{baz:[0-9]+}/{boom:[0-9]+}?ba=1&bo=2');
  expect(route('boom', ['baz' => 5]))->toBe('http://lumen.laravel.com/foo-bar/5');
});

it('register service provider', function () {
  $application = new Application;
  $lumenTestServiceProvider = new LumenTestServiceProvider($application);
  $application->register($lumenTestServiceProvider);

  expect(true)->toBeTrue();
});

it('application boots service providers on boot', function () {
  $application = new Application;

  $lumenBootableTestServiceProvider = new LumenBootableTestServiceProvider($application);
  $application->register($lumenBootableTestServiceProvider);

  expect($lumenBootableTestServiceProvider->booted)->toBeFalse();
  $application->boot();
  expect($lumenBootableTestServiceProvider->booted)->toBeTrue();
});

it('register service provider after boot', function () {
  $application = new Application;
  $lumenBootableTestServiceProvider = new LumenBootableTestServiceProvider($application);
  $application->boot();
  $application->register($lumenBootableTestServiceProvider);
  expect($lumenBootableTestServiceProvider->booted)->toBeTrue();
});

it('application boots only once', function () {
  $application = new Application;
  $provider = new class($application) extends ServiceProvider
  {
    public $bootCount = 0;

    public function boot(): void
    {
      $this->bootCount += 1;
    }
  };

  $application->register($provider);
  $application->boot();
  $application->boot();
  expect($provider->bootCount)->toBe(1);
});

it('application boots when request is dispatched', function () {
  $application = new Application;
  $lumenBootableTestServiceProvider = new LumenBootableTestServiceProvider($application);
  $application->register($lumenBootableTestServiceProvider);
  $application->dispatch();
  expect($lumenBootableTestServiceProvider->booted)->toBeTrue();
});

it('using custom dispatcher', function () {
  $routeCollector = new RouteCollector(new Std, new GroupCountBased);

  $routeCollector->addRoute('GET', '/', [fn () => response('Hello World')]);

  $application = new Application;

  $application->setDispatcher(new FastRoute\Dispatcher\GroupCountBased($routeCollector->getData()));

  $response = $application->handle(Request::create('/', 'GET'));

  expect($response->getStatusCode())->toBe(200);
  expect($response->getContent())->toBe('Hello World');
});

it('middleware receive responses even when string returned', function () {
  unset($_SERVER['__middleware.response']);

  $application = new Application;

  $application->routeMiddleware(['foo' => 'LumenTestPlainMiddleware']);

  $application->router->get('/', ['middleware' => 'foo', fn (): string => 'Hello World']);

  $response = $application->handle(Request::create('/', 'GET'));
  expect($response->getStatusCode())->toBe(200);
  expect($response->getContent())->toBe('Hello World');
  expect($_SERVER['__middleware.response'])->toBeTrue();
});

it('basic controller dispatching', function () {
  $application = new Application;

  $application->router->get('/show/{id}', 'LumenTestController@show');

  $response = $application->handle(Request::create('/show/25', 'GET'));

  expect($response->getStatusCode())->toBe(200);
  expect($response->getContent())->toBe('25');
});

it('basic controller dispatching with group', function () {
  $application = new Application;
  $application->routeMiddleware(['test' => LumenTestMiddleware::class]);

  $application->router->group(['middleware' => 'test'], function ($router): void {
    $router->get('/show/{id}', 'LumenTestController@show');
  });

  $response = $application->handle(Request::create('/show/25', 'GET'));

  expect($response->getStatusCode())->toBe(200);
  expect($response->getContent())->toBe('Middleware');
});

it('basic controller dispatching with group suffix', function () {
  $application = new Application;
  $application->routeMiddleware(['test' => LumenTestMiddleware::class]);

  $application->router->group(['suffix' => '.{format:json|xml}'], function ($router): void {
    $router->get('/show/{id}', 'LumenTestController@show');
  });

  $response = $application->handle(Request::create('/show/25.xml', 'GET'));

  expect($response->getStatusCode())->toBe(200);
  expect($response->getContent())->toBe('25');
});

it('basic controller dispatching with group and suffix with path', function () {
  $application = new Application;
  $application->routeMiddleware(['test' => LumenTestMiddleware::class]);

  $application->router->group(['suffix' => '/{format:json|xml}'], function ($router): void {
    $router->get('/show/{id}', 'LumenTestController@show');
  });

  $response = $application->handle(Request::create('/show/test/json', 'GET'));

  expect($response->getStatusCode())->toBe(200);
  expect($response->getContent())->toBe('test');
});

it('basic controller dispatching with middleware intercept', function () {
  $application = new Application;
  $application->routeMiddleware(['test' => LumenTestMiddleware::class]);
  $application->router->get('/show/{id}', 'LumenTestControllerWithMiddleware@show');

  $response = $application->handle(Request::create('/show/25', 'GET'));

  expect($response->getStatusCode())->toBe(200);
  expect($response->getContent())->toBe('Middleware');
});

it('basic invokable action dispatching', function () {
  $application = new Application;

  $application->router->get('/action/{id}', 'LumenTestAction');

  $response = $application->handle(Request::create('/action/199', 'GET'));

  expect($response->getStatusCode())->toBe(200);
  expect($response->getContent())->toBe('199');
});

it('environment detection', function () {
  $application = new Application;

  expect($application->environment())->toBe('production');
  expect($application->environment('production'))->toBeTrue();
  expect($application->environment(['production']))->toBeTrue();
});

it('namespace detection', function () {
  $application = new Application;
  expect(fn () => $application->getNamespace())->toThrow('RuntimeException');
});

it('running unit tests detection', function () {
  $application = new Application;

  expect($application->runningUnitTests())->toBeFalse();
});

it('validation helpers', function () {
  $application = new Application;

  $application->router->get('/', function (Illuminate\Http\Request $request) use ($application) {
    // Use the application's validate method through the ProvidesConvenienceMethods trait
    $validator = $application->make('validator')->make($request->all(), ['name' => 'required']);

    if ($validator->fails()) {
      return response()->json($validator->errors(), 422);
    }

    return response()->json($request->only(['name']));
  });

  $response = $application->handle(Request::create('/', 'GET'));

  expect($response->getStatusCode())->toBe(422);

  $response = $application->handle(Request::create('/', 'GET', ['name' => 'Jon']));

  expect($response->getStatusCode())->toBe(200);
  expect($response->getContent())->toBe('{"name":"Jon"}');
});

it('redirect response', function () {
  $application = new Application;

  $application->router->get('/', function (Illuminate\Http\Request $request) {
    unset($request); // Suppress unused parameter warning

    return redirect('home');
  });

  $response = $application->handle(Request::create('/', 'GET'));

  expect($response->getStatusCode())->toBe(302);
});

it('redirect to named route', function () {
  $application = new Application;

  $application->router->get('login', ['as' => 'login', function (Illuminate\Http\Request $request): string {
    unset($request); // Suppress unused parameter warning

    return 'login';
  }]);

  $application->router->get('/', function (Illuminate\Http\Request $request) {
    unset($request); // Suppress unused parameter warning

    return redirect()->route('login');
  });

  $response = $application->handle(Request::create('/', 'GET'));

  expect($response->getStatusCode())->toBe(302);
});

it('request user', function () {
  $application = new Application;

  $application['auth']->viaRequest('api', function ($request): GenericUser {
    unset($request); // Suppress unused parameter warning

    return new GenericUser(['id' => 1234]);
  });

  $application->router->get('/', fn (Illuminate\Http\Request $request) => $request->user()->getAuthIdentifier());

  $response = $application->handle(Request::create('/', 'GET'));

  expect($response->getContent())->toBe('1234');
});

it('can resolve filesystem factory from contract', function () {
  $application = new Application;

  $filesystem = $application[Illuminate\Contracts\Filesystem\Factory::class];

  expect($filesystem)->toBeInstanceOf(Illuminate\Contracts\Filesystem\Factory::class);
});

it('can resolve validation factory from contract', function () {
  $application = new Application;

  $validator = $application[Factory::class];

  expect($validator)->toBeInstanceOf(Factory::class);
});

it('can merge user provided facades with default ones', function () {
  $application = new Application;

  $aliases = [
    UserFacade::class => 'Foo',
  ];

  $application->withFacades(true, $aliases);

  // The alias should be created and the class should exist
  expect(class_exists('Foo'))->toBeTrue();
});

it('nested group middlewares request', function () {
  $application = new Application;

  $application->router->group(['middleware' => 'middleware1'], function ($router): void {
    $router->group(['middleware' => 'middleware2|middleware3'], function ($router): void {
      $router->get('test', 'LumenTestController@show');
    });
  });

  $route = $application->router->getRoutes()['GET/test'];

  expect($route['action']['middleware'])->toBe([
    'middleware1',
    'middleware2',
    'middleware3',
  ]);
});

it('nested group namespace request', function () {
  $application = new Application;

  $application->router->group(['namespace' => 'Hello'], function ($router): void {
    $router->group(['namespace' => 'World'], function ($router): void {
      $router->get('/world', 'Class@method');
    });
  });

  $routes = $application->router->getRoutes();

  $route = $routes['GET/world'];

  expect($route['action']['uses'])->toBe('Hello\\World\\Class@method');
});

it('nested group namespace with FQCN class name', function () {
  $application = new Application;

  $application->router->group(['namespace' => 'Hello'], function ($router): void {
    $router->group(['namespace' => 'World'], function ($router): void {
      $router->get('/world', '\Global\Namespaced\Class@method');
    });
  });

  $routes = $application->router->getRoutes();

  $route = $routes['GET/world'];

  expect($route['action']['uses'])->toBe('\\Global\\Namespaced\\Class@method');
});

it('nested group prefix request', function () {
  $application = new Application;

  $application->router->group(['prefix' => 'hello'], function ($router): void {
    $router->group(['prefix' => 'world'], function ($router): void {
      $router->get('/world', 'Class@method');
    });
  });

  $routes = $application->router->getRoutes();

  expect($routes)->toHaveKey('GET/hello/world/world');
});

it('nested group as request', function () {
  $application = new Application;

  $application->router->group(['as' => 'hello'], function ($router): void {
    $router->group(['as' => 'world'], function ($router): void {
      $router->get('/world', 'Class@method');
    });
  });

  expect($application->router->namedRoutes)->toHaveKey('hello.world');
  expect($application->router->namedRoutes['hello.world'])->toBe('/world');
});

it('container bindings are not overwritten', function () {
  $application = new Application;

  $mock = m::mock(Dispatcher::class);

  $application->instance(Illuminate\Contracts\Bus\Dispatcher::class, $mock);

  expect($application->make(Illuminate\Contracts\Bus\Dispatcher::class))->toBe($mock);
});

it('application class can be overwritten', function () {
  $lumenTestApplication = new LumenTestApplication;

  expect($lumenTestApplication->make(Application::class))->toBeInstanceOf(LumenTestApplication::class);
});

it('request is rebound on dispatch', function () {
  $application = new Application;
  $rebound = false;
  $application->rebinding('request', function () use (&$rebound): void {
    $rebound = true;
  });
  $application->handle(Request::create('/'));
  expect($rebound)->toBeTrue();
});

it('batches table command is registered', function () {
  $lumenTestApplication = new LumenTestApplication;
  $lumenTestApplication->register(ConsoleServiceProvider::class);
  $command = $lumenTestApplication->make('command.queue.batches-table');
  expect($command)->not->toBeNull();
  expect($command->getName())->toBe('make:queue-batches-table');
});

it('handling commands terminates application', function () {
  $lumenTestApplication = new LumenTestApplication;
  $lumenTestApplication->register(ConsoleServiceProvider::class);
  $lumenTestApplication->register(ViewServiceProvider::class);

  $lumenTestApplication->instance(ExceptionHandler::class, $mock = m::mock('Laravel\Lumen\Exceptions\Handler[report]'));
  $mock->shouldIgnoreMissing();

  $kernel = $lumenTestApplication[Kernel::class];

  (fn () => $kernel->getArtisan())->call($kernel)->resolveCommands(
    SendEmails::class,
  );

  $terminated = false;
  $lumenTestApplication->terminating(function () use (&$terminated): void {
    $terminated = true;
  });

  $arrayInput = new ArrayInput(['command' => 'send:emails']);

  $kernel->handle($arrayInput, new NullOutput);

  expect($terminated)->toBeTrue();
});

it('termination tests', function () {
  $lumenTestApplication = new LumenTestApplication;

  $result = [];
  $callback1 = function () use (&$result): void {
    $result[] = 1;
  };

  $callback2 = function () use (&$result): void {
    $result[] = 2;
  };

  $callback3 = function () use (&$result): void {
    $result[] = 3;
  };

  $lumenTestApplication->terminating($callback1);
  $lumenTestApplication->terminating($callback2);
  $lumenTestApplication->terminating($callback3);

  $lumenTestApplication->terminate();

  expect($result)->toBe([1, 2, 3]);
});

class LumenTestService {}

class LumenTestServiceProvider extends ServiceProvider
{
  public function register() {}
}

class LumenBootableTestServiceProvider extends ServiceProvider
{
  public $booted = false;

  public function boot(): void
  {
    $this->booted = true;
  }
}

class LumenTestController
{
  public function __construct()
  {
    //
  }

  public function show($id)
  {
    return $id;
  }
}

class LumenTestControllerWithMiddleware extends Controller
{
  public function __construct()
  {
    $this->middleware('test');
  }

  public function show($id)
  {
    return $id;
  }
}

class LumenTestMiddleware
{
  public function handle($request, $next)
  {
    unset($request, $next); // Suppress unused parameter warnings

    return response('Middleware');
  }
}

class LumenTestPlainMiddleware
{
  public function handle($request, $next)
  {
    $response = $next($request);
    $_SERVER['__middleware.response'] = $response instanceof Response;

    return $response;
  }
}

class LumenTestParameterizedMiddleware
{
  public function handle($request, $next, $parameter1, $parameter2)
  {
    unset($request, $next); // Suppress unused parameter warnings

    return response("Middleware - $parameter1 - $parameter2");
  }
}

class LumenTestAction
{
  public function __invoke($id)
  {
    return $id;
  }
}

class LumenTestApplication extends Application
{
  public function version(): string
  {
    return 'Custom Lumen App';
  }
}

class UserFacade {}

class LumenTestTerminateMiddleware
{
  public function handle($request, $next)
  {
    return $next($request);
  }

  public function terminate($request, Response $response): void
  {
    unset($request); // Suppress unused parameter warning
    $response->setContent('TERMINATED');
  }
}

class ResponsableResponse implements Responsable
{
  public function toResponse($request)
  {
    return $request->route('foo');
  }
}

class SendEmails extends Command
{
  protected $signature = 'send:emails';

  public function handle(): void
  {
    // ..
  }
}
