<?php

namespace Kasi\Tests\Foundation\Console;

use Kasi\Console\Application;
use Kasi\Contracts\Events\Dispatcher;
use Kasi\Foundation\Console\RouteListCommand;
use Kasi\Foundation\Http\Kernel;
use Kasi\Routing\Router;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class RouteListCommandTest extends TestCase
{
    protected Application $app;

    protected function tearDown(): void
    {
        m::close();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->app = new Application(
            $kasi = new \Kasi\Foundation\Application(__DIR__),
            m::mock(Dispatcher::class, ['dispatch' => null, 'fire' => null]),
            'testing',
        );

        $router = new Router(m::mock('Kasi\Events\Dispatcher'));

        $kernel = new class($kasi, $router) extends Kernel
        {
            protected $middlewareGroups = [
                'web' => ['Middleware 1', 'Middleware 2', 'Middleware 5'],
                'auth' => ['Middleware 3', 'Middleware 4'],
            ];

            protected $middlewarePriority = [
                'Middleware 1',
                'Middleware 4',
                'Middleware 2',
                'Middleware 3',
            ];
        };

        $kernel->prependToMiddlewarePriority('Middleware 5');

        $kasi->instance(Kernel::class, $kernel);

        $router->get('/example', function () {
            return 'Hello World';
        })->middleware('exampleMiddleware');

        $router->get('/sub-example', function () {
            return 'Hello World';
        })->domain('sub')
            ->middleware('exampleMiddleware');

        $router->get('/example-group', function () {
            return 'Hello Group';
        })->middleware(['web', 'auth']);

        $command = new RouteListCommand($router);
        $command->setKasi($kasi);

        $this->app->addCommands([$command]);
    }

    public function testNoMiddlewareIfNotVerbose()
    {
        $this->app->call('route:list');
        $output = $this->app->output();

        $this->assertStringNotContainsString('exampleMiddleware', $output);
    }

    public function testSortRouteListAsc()
    {
        $this->app->call('route:list', ['--json' => true, '--sort' => 'domain,uri']);
        $output = $this->app->output();

        $expectedOrder = '[{"domain":null,"method":"GET|HEAD","uri":"example","name":null,"action":"Closure","middleware":["exampleMiddleware"]},{"domain":null,"method":"GET|HEAD","uri":"example-group","name":null,"action":"Closure","middleware":["web","auth"]},{"domain":"sub","method":"GET|HEAD","uri":"sub-example","name":null,"action":"Closure","middleware":["exampleMiddleware"]}]';

        $this->assertJsonStringEqualsJsonString($expectedOrder, $output);
    }

    public function testSortRouteListDesc()
    {
        $this->app->call('route:list', ['--json' => true, '--sort' => 'domain,uri', '--reverse' => true]);
        $output = $this->app->output();

        $expectedOrder = '[{"domain":"sub","method":"GET|HEAD","uri":"sub-example","name":null,"action":"Closure","middleware":["exampleMiddleware"]},{"domain":null,"method":"GET|HEAD","uri":"example-group","name":null,"action":"Closure","middleware":["web","auth"]},{"domain":null,"method":"GET|HEAD","uri":"example","name":null,"action":"Closure","middleware":["exampleMiddleware"]}]';

        $this->assertJsonStringEqualsJsonString($expectedOrder, $output);
    }

    public function testSortRouteListDefault()
    {
        $this->app->call('route:list', ['--json' => true]);
        $output = $this->app->output();

        $expectedOrder = '[{"domain":null,"method":"GET|HEAD","uri":"example","name":null,"action":"Closure","middleware":["exampleMiddleware"]},{"domain":null,"method":"GET|HEAD","uri":"example-group","name":null,"action":"Closure","middleware":["web","auth"]}, {"domain":"sub","method":"GET|HEAD","uri":"sub-example","name":null,"action":"Closure","middleware":["exampleMiddleware"]}]';

        $this->assertJsonStringEqualsJsonString($expectedOrder, $output);
    }

    public function testSortRouteListPrecedence()
    {
        $this->app->call('route:list', ['--json' => true, '--sort' => 'definition']);
        $output = $this->app->output();

        $expectedOrder = '[{"domain":null,"method":"GET|HEAD","uri":"example","name":null,"action":"Closure","middleware":["exampleMiddleware"]},{"domain":"sub","method":"GET|HEAD","uri":"sub-example","name":null,"action":"Closure","middleware":["exampleMiddleware"]}, {"domain":null,"method":"GET|HEAD","uri":"example-group","name":null,"action":"Closure","middleware":["web","auth"]}]';

        $this->assertJsonStringEqualsJsonString($expectedOrder, $output);
    }

    public function testMiddlewareGroupsAssignmentInCli()
    {
        $this->app->call('route:list', ['-v' => true]);
        $output = $this->app->output();

        $this->assertStringContainsString('exampleMiddleware', $output);
        $this->assertStringContainsString('web', $output);
        $this->assertStringContainsString('auth', $output);

        $this->assertStringNotContainsString('Middleware 1', $output);
        $this->assertStringNotContainsString('Middleware 2', $output);
        $this->assertStringNotContainsString('Middleware 3', $output);
        $this->assertStringNotContainsString('Middleware 4', $output);
        $this->assertStringNotContainsString('Middleware 5', $output);
    }

    public function testMiddlewareGroupsExpandInCliIfVeryVerbose()
    {
        $this->app->call('route:list', ['-vv' => true]);
        $output = $this->app->output();

        $this->assertStringContainsString('exampleMiddleware', $output);
        $this->assertStringContainsString('Middleware 1', $output);
        $this->assertStringContainsString('Middleware 2', $output);
        $this->assertStringContainsString('Middleware 3', $output);
        $this->assertStringContainsString('Middleware 4', $output);
        $this->assertStringContainsString('Middleware 5', $output);

        $this->assertStringNotContainsString('web', $output);
        $this->assertStringNotContainsString('auth', $output);
    }

    public function testMiddlewareGroupsAssignmentInJson()
    {
        $this->app->call('route:list', ['--json' => true, '-v' => true]);
        $output = $this->app->output();

        $this->assertStringContainsString('exampleMiddleware', $output);
        $this->assertStringContainsString('web', $output);
        $this->assertStringContainsString('auth', $output);

        $this->assertStringNotContainsString('Middleware 1', $output);
        $this->assertStringNotContainsString('Middleware 2', $output);
        $this->assertStringNotContainsString('Middleware 3', $output);
        $this->assertStringNotContainsString('Middleware 4', $output);
        $this->assertStringNotContainsString('Middleware 5', $output);
    }

    public function testMiddlewareGroupsExpandInJsonIfVeryVerbose()
    {
        $this->app->call('route:list', ['--json' => true, '-vv' => true]);
        $output = $this->app->output();

        $this->assertStringContainsString('exampleMiddleware', $output);
        $this->assertStringContainsString('Middleware 1', $output);
        $this->assertStringContainsString('Middleware 2', $output);
        $this->assertStringContainsString('Middleware 3', $output);
        $this->assertStringContainsString('Middleware 4', $output);
        $this->assertStringContainsString('Middleware 5', $output);

        $this->assertStringNotContainsString('web', $output);
        $this->assertStringNotContainsString('auth', $output);
    }

    public function testMiddlewareGroupsExpandCorrectlySortedIfVeryVerbose()
    {
        $this->app->call('route:list', ['--json' => true, '-vv' => true]);
        $output = $this->app->output();

        $expectedOrder = '[{"domain":null,"method":"GET|HEAD","uri":"example","name":null,"action":"Closure","middleware":["exampleMiddleware"]},{"domain":null,"method":"GET|HEAD","uri":"example-group","name":null,"action":"Closure","middleware":["Middleware 5","Middleware 1","Middleware 4","Middleware 2","Middleware 3"]},{"domain":"sub","method":"GET|HEAD","uri":"sub-example","name":null,"action":"Closure","middleware":["exampleMiddleware"]}]';

        $this->assertJsonStringEqualsJsonString($expectedOrder, $output);
    }
}
