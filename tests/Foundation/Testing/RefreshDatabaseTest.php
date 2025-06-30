<?php

namespace Kasi\Tests\Foundation\Testing;

use Kasi\Contracts\Console\Kernel as ConsoleKernelContract;
use Kasi\Foundation\Console\Kernel as ConsoleKernel;
use Kasi\Foundation\Testing\Concerns\InteractsWithConsole;
use Kasi\Foundation\Testing\RefreshDatabase;
use Kasi\Foundation\Testing\RefreshDatabaseState;
use Mockery as m;
use Orchestra\Testbench\Concerns\ApplicationTestingHooks;
use Orchestra\Testbench\Foundation\Application as Testbench;
use PHPUnit\Framework\TestCase;

use function Orchestra\Testbench\package_path;

class RefreshDatabaseTest extends TestCase
{
    use ApplicationTestingHooks;
    use InteractsWithConsole;
    use RefreshDatabase;

    public $dropViews = false;

    public $dropTypes = false;

    protected function setUp(): void
    {
        RefreshDatabaseState::$migrated = false;

        $this->setUpTheApplicationTestingHooks();
        $this->withoutMockingConsoleOutput();
    }

    protected function tearDown(): void
    {
        $this->tearDownTheApplicationTestingHooks();

        RefreshDatabaseState::$migrated = false;
    }

    protected function refreshApplication()
    {
        $this->app = Testbench::create(
            basePath: package_path('vendor/orchestra/testbench-core/kasi'),
        );
    }

    public function testRefreshTestDatabaseDefault()
    {
        $this->app->instance(ConsoleKernelContract::class, $kernel = m::spy(ConsoleKernel::class));

        $kernel->shouldReceive('call')
            ->once()
            ->with('migrate:fresh', [
                '--drop-views' => false,
                '--drop-types' => false,
                '--seed' => false,
            ]);

        $this->refreshTestDatabase();
    }

    public function testRefreshTestDatabaseWithDropViewsOption()
    {
        $this->dropViews = true;

        $this->app->instance(ConsoleKernelContract::class, $kernel = m::spy(ConsoleKernel::class));

        $kernel->shouldReceive('call')
            ->once()
            ->with('migrate:fresh', [
                '--drop-views' => true,
                '--drop-types' => false,
                '--seed' => false,
            ]);

        $this->refreshTestDatabase();
    }

    public function testRefreshTestDatabaseWithDropTypesOption()
    {
        $this->dropTypes = true;

        $this->app->instance(ConsoleKernelContract::class, $kernel = m::spy(ConsoleKernel::class));

        $kernel->shouldReceive('call')
            ->once()
            ->with('migrate:fresh', [
                '--drop-views' => false,
                '--drop-types' => true,
                '--seed' => false,
            ]);

        $this->refreshTestDatabase();
    }
}
