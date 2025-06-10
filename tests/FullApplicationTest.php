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
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

class FullApplicationTest extends TestCase
{
  protected function setUp(): void
  {
    Facade::clearResolvedInstances();
  }

  protected function tearDown(): void
  {
    m::close();

    // Restore error handlers to prevent warnings
    restore_error_handler();
    restore_exception_handler();
  }

  public function testBasicRequest(): void
  {
    $application = new Application;

    $application->router->get('/', fn () => response('Hello World'));

    $response = $application->handle($request = Request::create('/', 'GET'));

    $this->assertEquals(200, $response->getStatusCode());
    $this->assertEquals('Hello World', $response->getContent());

    $this->assertInstanceOf(Request::class, $request);
  }

  public function testBasicSymfonyRequest(): void
  {
    $application = new Application;

    $application->router->get('/', fn () => response('Hello World'));

    $response = $application->handle(SymfonyRequest::create('/', 'GET'));
    $this->assertEquals(200, $response->getStatusCode());
  }

  public function testAddRouteMultipleMethodRequest(): void
  {
    $application = new Application;

    $application->router->addRoute(['GET', 'POST'], '/', fn () => response('Hello World'));

    $response = $application->handle(Request::create('/', 'GET'));

    $this->assertEquals(200, $response->getStatusCode());
    $this->assertEquals('Hello World', $response->getContent());

    $response = $application->handle(Request::create('/', 'POST'));

    $this->assertEquals(200, $response->getStatusCode());
    $this->assertEquals('Hello World', $response->getContent());
  }

  public function testRequestWithParameters(): void
  {
    $application = new Application;

    $application->router->get('/foo/{bar}/{baz}', fn ($bar, $baz) => response($bar.$baz));

    $response = $application->handle($request = Request::create('/foo/1/2', 'GET'));

    $this->assertEquals(200, $response->getStatusCode());
    $this->assertEquals('12', $response->getContent());

    $this->assertEquals(1, $request->route('bar'));
    $this->assertEquals(2, $request->route('baz'));
  }

  public function testCallbackRouteWithDefaultParameter(): void
  {
    $application = new Application;
    $application->router->get('/foo-bar/{baz}', fn ($baz = 'default-value') => response($baz));

    $response = $application->handle(Request::create('/foo-bar/something', 'GET'));

    $this->assertEquals(200, $response->getStatusCode());
    $this->assertEquals('something', $response->getContent());
  }

  public function testGlobalMiddleware(): void
  {
    $application = new Application;

    $application->middleware(['LumenTestMiddleware']);

    $application->router->get('/', fn () => response('Hello World'));

    $response = $application->handle(Request::create('/', 'GET'));

    $this->assertEquals(200, $response->getStatusCode());
    $this->assertEquals('Middleware', $response->getContent());
  }

  public function testRouteMiddleware(): void
  {
    $application = new Application;

    $application->routeMiddleware(['foo' => 'LumenTestMiddleware', 'passing' => 'LumenTestPlainMiddleware']);

    $application->router->get('/', fn () => response('Hello World'));

    $application->router->get('/foo', ['middleware' => 'foo', fn () => response('Hello World')]);

    $application->router->get('/bar', ['middleware' => ['foo'], fn () => response('Hello World')]);

    $application->router->get('/fooBar', ['middleware' => 'passing|foo', fn () => response('Hello World')]);

    $response = $application->handle(Request::create('/', 'GET'));
    $this->assertEquals(200, $response->getStatusCode());
    $this->assertEquals('Hello World', $response->getContent());

    $response = $application->handle(Request::create('/foo', 'GET'));
    $this->assertEquals(200, $response->getStatusCode());
    $this->assertEquals('Middleware', $response->getContent());

    $response = $application->handle(Request::create('/bar', 'GET'));
    $this->assertEquals(200, $response->getStatusCode());
    $this->assertEquals('Middleware', $response->getContent());

    $response = $application->handle(Request::create('/fooBar', 'GET'));
    $this->assertEquals(200, $response->getStatusCode());
    $this->assertEquals('Middleware', $response->getContent());
  }

  public function testGlobalMiddlewareParameters(): void
  {
    $application = new Application;

    $application->middleware(['LumenTestParameterizedMiddleware:foo,bar']);

    $application->router->get('/', fn () => response('Hello World'));

    $response = $application->handle(Request::create('/', 'GET'));

    $this->assertEquals(200, $response->getStatusCode());
    $this->assertEquals('Middleware - foo - bar', $response->getContent());
  }

  public function testRouteMiddlewareParameters(): void
  {
    $application = new Application;

    $application->routeMiddleware(['foo' => 'LumenTestParameterizedMiddleware', 'passing' => 'LumenTestPlainMiddleware']);

    $application->router->get('/', ['middleware' => 'passing|foo:bar,boom', fn () => response('Hello World')]);

    $response = $application->handle(Request::create('/', 'GET'));

    $this->assertEquals(200, $response->getStatusCode());
    $this->assertEquals('Middleware - bar - boom', $response->getContent());
  }

  public function testWithMiddlewareDisabled(): void
  {
    $application = new Application;

    $application->middleware(['LumenTestMiddleware']);
    $application->instance('middleware.disable', true);

    $application->router->get('/', fn () => response('Hello World'));

    $response = $application->handle(Request::create('/', 'GET'));

    $this->assertEquals(200, $response->getStatusCode());
    $this->assertEquals('Hello World', $response->getContent());
  }

  public function testTerminableGlobalMiddleware(): void
  {
    $application = new Application;

    $application->middleware(['LumenTestTerminateMiddleware']);

    $application->router->get('/', fn () => response('Hello World'));

    $response = $application->handle(Request::create('/', 'GET'));

    $this->assertEquals(200, $response->getStatusCode());
    $this->assertEquals('TERMINATED', $response->getContent());
  }

  public function testTerminateWithMiddlewareDisabled(): void
  {
    $application = new Application;

    $application->middleware(['LumenTestTerminateMiddleware']);
    $application->instance('middleware.disable', true);

    $application->router->get('/', fn () => response('Hello World'));

    $response = $application->handle(Request::create('/', 'GET'));

    $this->assertEquals(200, $response->getStatusCode());
    $this->assertEquals('Hello World', $response->getContent());
  }

  public function testNotFoundResponse(): void
  {
    $application = new Application;
    $application->instance(ExceptionHandler::class, $mock = m::mock('Laravel\Lumen\Exceptions\Handler[report]'));
    $mock->shouldIgnoreMissing();

    $application->router->get('/', fn () => response('Hello World'));

    $response = $application->handle(Request::create('/foo', 'GET'));

    $this->assertEquals(404, $response->getStatusCode());
  }

  public function testMethodNotAllowedResponse(): void
  {
    $application = new Application;
    $application->instance(ExceptionHandler::class, $mock = m::mock('Laravel\Lumen\Exceptions\Handler[report]'));
    $mock->shouldIgnoreMissing();

    $application->router->post('/', fn () => response('Hello World'));

    $response = $application->handle(Request::create('/', 'GET'));

    $this->assertEquals(405, $response->getStatusCode());
  }

  public function testResponsableInterface(): void
  {
    $application = new Application;

    $application->router->get('/foo/{foo}', fn (): \ResponsableResponse => new ResponsableResponse);

    $request = Request::create('/foo/999', 'GET');
    $response = $application->handle($request);

    $this->assertEquals(999, $request->route('foo'));
    $this->assertEquals(999, $response->original);
  }

  public function testUncaughtExceptionResponse(): void
  {
    $application = new Application;
    $application->instance(ExceptionHandler::class, $mock = m::mock('Laravel\Lumen\Exceptions\Handler[report]'));
    $mock->shouldIgnoreMissing();

    $application->router->get('/', function (): void {
      throw new RuntimeException('app exception');
    });

    $response = $application->handle(Request::create('/', 'GET'));
    $this->assertInstanceOf(Response::class, $response);
  }

  public function testGeneratingUrls(): void
  {
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

    $this->assertEquals('http://lumen.laravel.com/something', url('something'));
    $this->assertEquals('http://lumen.laravel.com/foo-bar', route('foo'));
    $this->assertEquals('http://lumen.laravel.com/foo-bar/1/2', route('bar', ['baz' => 1, 'boom' => 2]));
    $this->assertEquals('http://lumen.laravel.com/foo-bar?baz=1&boom=2', route('foo', ['baz' => 1, 'boom' => 2]));
    $this->assertEquals('http://lumen.laravel.com/foo-bar/1/2', route('optional', ['baz' => 1, 'boom' => 2]));
    $this->assertEquals('http://lumen.laravel.com/foo-bar/1', route('optional', ['baz' => 1]));
    $this->assertEquals('http://lumen.laravel.com/foo-bar/1/2', route('regex', ['baz' => 1, 'boom' => 2]));
    $this->assertEquals('http://lumen.laravel.com/foo-bar/1', route('regex', ['baz' => 1]));
  }

  public function testGeneratingUrlsForRegexParameters(): void
  {
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

    $this->assertEquals('http://lumen.laravel.com/something', url('something'));
    $this->assertEquals('http://lumen.laravel.com/foo-bar', route('foo'));
    $this->assertEquals('http://lumen.laravel.com/foo-bar/1/2', route('bar', ['baz' => 1, 'boom' => 2]));
    $this->assertEquals('http://lumen.laravel.com/foo-bar/1/2', route('baz', ['baz' => 1, 'boom' => 2]));
    $this->assertEquals('http://lumen.laravel.com/foo-bar/{baz:[0-9]+}/{boom:[0-9]+}?ba=1&bo=2', route('baz', ['ba' => 1, 'bo' => 2]));
    $this->assertEquals('http://lumen.laravel.com/foo-bar/5', route('boom', ['baz' => 5]));
  }

  public function testRegisterServiceProvider(): void
  {
    $application = new Application;
    $lumenTestServiceProvider = new LumenTestServiceProvider($application);
    $application->register($lumenTestServiceProvider);

    $this->assertTrue(true);
  }

  public function testApplicationBootsServiceProvidersOnBoot(): void
  {
    $application = new Application;

    $lumenBootableTestServiceProvider = new LumenBootableTestServiceProvider($application);
    $application->register($lumenBootableTestServiceProvider);

    $this->assertFalse($lumenBootableTestServiceProvider->booted);
    $application->boot();
    $this->assertTrue($lumenBootableTestServiceProvider->booted);
  }

  public function testRegisterServiceProviderAfterBoot(): void
  {
    $application = new Application;
    $lumenBootableTestServiceProvider = new LumenBootableTestServiceProvider($application);
    $application->boot();
    $application->register($lumenBootableTestServiceProvider);
    $this->assertTrue($lumenBootableTestServiceProvider->booted);
  }

  public function testApplicationBootsOnlyOnce(): void
  {
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
    $this->assertEquals(1, $provider->bootCount);
  }

  public function testApplicationBootsWhenRequestIsDispatched(): void
  {
    $application = new Application;
    $lumenBootableTestServiceProvider = new LumenBootableTestServiceProvider($application);
    $application->register($lumenBootableTestServiceProvider);
    $application->dispatch();
    $this->assertTrue($lumenBootableTestServiceProvider->booted);
  }

  public function testUsingCustomDispatcher(): void
  {
    $routeCollector = new RouteCollector(new Std, new GroupCountBased);

    $routeCollector->addRoute('GET', '/', [fn () => response('Hello World')]);

    $application = new Application;

    $application->setDispatcher(new FastRoute\Dispatcher\GroupCountBased($routeCollector->getData()));

    $response = $application->handle(Request::create('/', 'GET'));

    $this->assertEquals(200, $response->getStatusCode());
    $this->assertEquals('Hello World', $response->getContent());
  }

  public function testMiddlewareReceiveResponsesEvenWhenStringReturned(): void
  {
    unset($_SERVER['__middleware.response']);

    $application = new Application;

    $application->routeMiddleware(['foo' => 'LumenTestPlainMiddleware']);

    $application->router->get('/', ['middleware' => 'foo', fn (): string => 'Hello World']);

    $response = $application->handle(Request::create('/', 'GET'));
    $this->assertEquals(200, $response->getStatusCode());
    $this->assertEquals('Hello World', $response->getContent());
    $this->assertTrue($_SERVER['__middleware.response']);
  }

  public function testBasicControllerDispatching(): void
  {
    $application = new Application;

    $application->router->get('/show/{id}', 'LumenTestController@show');

    $response = $application->handle(Request::create('/show/25', 'GET'));

    $this->assertEquals(200, $response->getStatusCode());
    $this->assertEquals('25', $response->getContent());
  }

  public function testBasicControllerDispatchingWithGroup(): void
  {
    $application = new Application;
    $application->routeMiddleware(['test' => LumenTestMiddleware::class]);

    $application->router->group(['middleware' => 'test'], function ($router): void {
      $router->get('/show/{id}', 'LumenTestController@show');
    });

    $response = $application->handle(Request::create('/show/25', 'GET'));

    $this->assertEquals(200, $response->getStatusCode());
    $this->assertEquals('Middleware', $response->getContent());
  }

  public function testBasicControllerDispatchingWithGroupSuffix(): void
  {
    $application = new Application;
    $application->routeMiddleware(['test' => LumenTestMiddleware::class]);

    $application->router->group(['suffix' => '.{format:json|xml}'], function ($router): void {
      $router->get('/show/{id}', 'LumenTestController@show');
    });

    $response = $application->handle(Request::create('/show/25.xml', 'GET'));

    $this->assertEquals(200, $response->getStatusCode());
    $this->assertEquals('25', $response->getContent());
  }

  public function testBasicControllerDispatchingWithGroupAndSuffixWithPath(): void
  {
    $application = new Application;
    $application->routeMiddleware(['test' => LumenTestMiddleware::class]);

    $application->router->group(['suffix' => '/{format:json|xml}'], function ($router): void {
      $router->get('/show/{id}', 'LumenTestController@show');
    });

    $response = $application->handle(Request::create('/show/test/json', 'GET'));

    $this->assertEquals(200, $response->getStatusCode());
    $this->assertEquals('test', $response->getContent());
  }

  public function testBasicControllerDispatchingWithMiddlewareIntercept(): void
  {
    $application = new Application;
    $application->routeMiddleware(['test' => LumenTestMiddleware::class]);
    $application->router->get('/show/{id}', 'LumenTestControllerWithMiddleware@show');

    $response = $application->handle(Request::create('/show/25', 'GET'));

    $this->assertEquals(200, $response->getStatusCode());
    $this->assertEquals('Middleware', $response->getContent());
  }

  public function testBasicInvokableActionDispatching(): void
  {
    $application = new Application;

    $application->router->get('/action/{id}', 'LumenTestAction');

    $response = $application->handle(Request::create('/action/199', 'GET'));

    $this->assertEquals(200, $response->getStatusCode());
    $this->assertEquals('199', $response->getContent());
  }

  public function testEnvironmentDetection(): void
  {
    $application = new Application;

    $this->assertEquals('production', $application->environment());
    $this->assertTrue($application->environment('production'));
    $this->assertTrue($application->environment(['production']));
  }

  public function testNamespaceDetection(): void
  {
    $application = new Application;
    $this->expectException('RuntimeException');
    $application->getNamespace();
  }

  public function testRunningUnitTestsDetection(): void
  {
    $application = new Application;

    $this->assertFalse($application->runningUnitTests());
  }

  public function testValidationHelpers(): void
  {
    $application = new Application;

    $application->router->get('/', fn (Illuminate\Http\Request $request) => $this->validate($request, ['name' => 'required']));

    $response = $application->handle(Request::create('/', 'GET'));

    $this->assertEquals(422, $response->getStatusCode());

    $response = $application->handle(Request::create('/', 'GET', ['name' => 'Jon']));

    $this->assertEquals(200, $response->getStatusCode());
    $this->assertEquals($response->getContent(), '{"name":"Jon"}');
  }

  public function testRedirectResponse(): void
  {
    $application = new Application;

    $application->router->get('/', fn (Illuminate\Http\Request $request) => redirect('home'));

    $response = $application->handle(Request::create('/', 'GET'));

    $this->assertEquals(302, $response->getStatusCode());
  }

  public function testRedirectToNamedRoute(): void
  {
    $application = new Application;

    $application->router->get('login', ['as' => 'login', fn (Illuminate\Http\Request $request): string => 'login']);

    $application->router->get('/', fn (Illuminate\Http\Request $request) => redirect()->route('login'));

    $response = $application->handle(Request::create('/', 'GET'));

    $this->assertEquals(302, $response->getStatusCode());
  }

  public function testRequestUser(): void
  {
    $application = new Application;

    $application['auth']->viaRequest('api', fn ($request): GenericUser => new GenericUser(['id' => 1234]));

    $application->router->get('/', fn (Illuminate\Http\Request $request) => $request->user()->getAuthIdentifier());

    $response = $application->handle(Request::create('/', 'GET'));

    $this->assertSame('1234', $response->getContent());
  }

  public function testCanResolveFilesystemFactoryFromContract(): void
  {
    $application = new Application;

    $filesystem = $application[Illuminate\Contracts\Filesystem\Factory::class];

    $this->assertInstanceOf(Illuminate\Contracts\Filesystem\Factory::class, $filesystem);
  }

  public function testCanResolveValidationFactoryFromContract(): void
  {
    $application = new Application;

    $validator = $application[Factory::class];

    $this->assertInstanceOf(Factory::class, $validator);
  }

  public function testCanMergeUserProvidedFacadesWithDefaultOnes(): void
  {
    $application = new Application;

    $aliases = [
      UserFacade::class => 'Foo',
    ];

    $application->withFacades(true, $aliases);

    $this->assertTrue(class_exists('Foo'));
  }

  public function testNestedGroupMiddlewaresRequest(): void
  {
    $application = new Application;

    $application->router->group(['middleware' => 'middleware1'], function ($router): void {
      $router->group(['middleware' => 'middleware2|middleware3'], function ($router): void {
        $router->get('test', 'LumenTestController@show');
      });
    });

    $route = $application->router->getRoutes()['GET/test'];

    $this->assertEquals([
      'middleware1',
      'middleware2',
      'middleware3',
    ], $route['action']['middleware']);
  }

  public function testNestedGroupNamespaceRequest(): void
  {
    $application = new Application;

    $application->router->group(['namespace' => 'Hello'], function ($router): void {
      $router->group(['namespace' => 'World'], function ($router): void {
        $router->get('/world', 'Class@method');
      });
    });

    $routes = $application->router->getRoutes();

    $route = $routes['GET/world'];

    $this->assertEquals('Hello\\World\\Class@method', $route['action']['uses']);
  }

  public function testNestedGroupNamespaceWithFQCNClassName(): void
  {
    $application = new Application;

    $application->router->group(['namespace' => 'Hello'], function ($router): void {
      $router->group(['namespace' => 'World'], function ($router): void {
        $router->get('/world', '\Global\Namespaced\Class@method');
      });
    });

    $routes = $application->router->getRoutes();

    $route = $routes['GET/world'];

    $this->assertEquals('\\Global\\Namespaced\\Class@method', $route['action']['uses']);
  }

  public function testNestedGroupPrefixRequest(): void
  {
    $application = new Application;

    $application->router->group(['prefix' => 'hello'], function ($router): void {
      $router->group(['prefix' => 'world'], function ($router): void {
        $router->get('/world', 'Class@method');
      });
    });

    $routes = $application->router->getRoutes();

    $this->assertArrayHasKey('GET/hello/world/world', $routes);
  }

  public function testNestedGroupAsRequest(): void
  {
    $application = new Application;

    $application->router->group(['as' => 'hello'], function ($router): void {
      $router->group(['as' => 'world'], function ($router): void {
        $router->get('/world', 'Class@method');
      });
    });

    $this->assertArrayHasKey('hello.world', $application->router->namedRoutes);
    $this->assertEquals('/world', $application->router->namedRoutes['hello.world']);
  }

  public function testContainerBindingsAreNotOverwritten(): void
  {
    $application = new Application;

    $mock = m::mock(Dispatcher::class);

    $application->instance(Illuminate\Contracts\Bus\Dispatcher::class, $mock);

    $this->assertSame(
      $mock,
      $application->make(Illuminate\Contracts\Bus\Dispatcher::class)
    );
  }

  public function testApplicationClassCanBeOverwritten(): void
  {
    $lumenTestApplication = new LumenTestApplication;

    $this->assertInstanceOf(LumenTestApplication::class, $lumenTestApplication->make(Application::class));
  }

  public function testRequestIsReboundOnDispatch(): void
  {
    $application = new Application;
    $rebound = false;
    $application->rebinding('request', function () use (&$rebound): void {
      $rebound = true;
    });
    $application->handle(Request::create('/'));
    $this->assertTrue($rebound);
  }

  public function testBatchesTableCommandIsRegistered(): void
  {
    $lumenTestApplication = new LumenTestApplication;
    $lumenTestApplication->register(ConsoleServiceProvider::class);
    $command = $lumenTestApplication->make('command.queue.batches-table');
    $this->assertNotNull($command);
    $this->assertEquals('make:queue-batches-table', $command->getName());
  }

  public function testHandlingCommandsTerminatesApplication(): void
  {
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

    $this->assertTrue($terminated);
  }

  public function testTerminationTests(): void
  {
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

    $this->assertEquals([1, 2, 3], $result);
  }
}

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
