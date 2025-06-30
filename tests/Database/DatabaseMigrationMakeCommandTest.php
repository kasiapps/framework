<?php

namespace Kasi\Tests\Database;

use Kasi\Database\Console\Migrations\MigrateMakeCommand;
use Kasi\Database\Migrations\MigrationCreator;
use Kasi\Foundation\Application;
use Kasi\Support\Composer;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

class DatabaseMigrationMakeCommandTest extends TestCase
{
    protected function tearDown(): void
    {
        m::close();
    }

    public function testBasicCreateDumpsAutoload()
    {
        $command = new MigrateMakeCommand(
            $creator = m::mock(MigrationCreator::class),
            $composer = m::mock(Composer::class)
        );
        $app = new Application;
        $app->useDatabasePath(__DIR__);
        $command->setKasi($app);
        $creator->shouldReceive('create')->once()
            ->with('create_foo', __DIR__.DIRECTORY_SEPARATOR.'migrations', 'foo', true)
            ->andReturn(__DIR__.'/migrations/2021_04_23_110457_create_foo.php');

        $this->runCommand($command, ['name' => 'create_foo']);
    }

    public function testBasicCreateGivesCreatorProperArguments()
    {
        $command = new MigrateMakeCommand(
            $creator = m::mock(MigrationCreator::class),
            m::mock(Composer::class)->shouldIgnoreMissing()
        );
        $app = new Application;
        $app->useDatabasePath(__DIR__);
        $command->setKasi($app);
        $creator->shouldReceive('create')->once()
            ->with('create_foo', __DIR__.DIRECTORY_SEPARATOR.'migrations', 'foo', true)
            ->andReturn(__DIR__.'/migrations/2021_04_23_110457_create_foo.php');

        $this->runCommand($command, ['name' => 'create_foo']);
    }

    public function testBasicCreateGivesCreatorProperArgumentsWhenNameIsStudlyCase()
    {
        $command = new MigrateMakeCommand(
            $creator = m::mock(MigrationCreator::class),
            m::mock(Composer::class)->shouldIgnoreMissing()
        );
        $app = new Application;
        $app->useDatabasePath(__DIR__);
        $command->setKasi($app);
        $creator->shouldReceive('create')->once()
            ->with('create_foo', __DIR__.DIRECTORY_SEPARATOR.'migrations', 'foo', true)
            ->andReturn(__DIR__.'/migrations/2021_04_23_110457_create_foo.php');

        $this->runCommand($command, ['name' => 'CreateFoo']);
    }

    public function testBasicCreateGivesCreatorProperArgumentsWhenTableIsSet()
    {
        $command = new MigrateMakeCommand(
            $creator = m::mock(MigrationCreator::class),
            m::mock(Composer::class)->shouldIgnoreMissing()
        );
        $app = new Application;
        $app->useDatabasePath(__DIR__);
        $command->setKasi($app);
        $creator->shouldReceive('create')->once()
            ->with('create_foo', __DIR__.DIRECTORY_SEPARATOR.'migrations', 'users', true)
            ->andReturn(__DIR__.'/migrations/2021_04_23_110457_create_foo.php');

        $this->runCommand($command, ['name' => 'create_foo', '--create' => 'users']);
    }

    public function testBasicCreateGivesCreatorProperArgumentsWhenCreateTablePatternIsFound()
    {
        $command = new MigrateMakeCommand(
            $creator = m::mock(MigrationCreator::class),
            m::mock(Composer::class)->shouldIgnoreMissing()
        );
        $app = new Application;
        $app->useDatabasePath(__DIR__);
        $command->setKasi($app);
        $creator->shouldReceive('create')->once()
            ->with('create_users_table', __DIR__.DIRECTORY_SEPARATOR.'migrations', 'users', true)
            ->andReturn(__DIR__.'/migrations/2021_04_23_110457_create_users_table.php');

        $this->runCommand($command, ['name' => 'create_users_table']);
    }

    public function testCanSpecifyPathToCreateMigrationsIn()
    {
        $command = new MigrateMakeCommand(
            $creator = m::mock(MigrationCreator::class),
            m::mock(Composer::class)->shouldIgnoreMissing()
        );
        $app = new Application;
        $command->setKasi($app);
        $app->setBasePath('/home/kasi');
        $creator->shouldReceive('create')->once()
            ->with('create_foo', '/home/kasi/vendor/kasi-package/migrations', 'users', true)
            ->andReturn('/home/kasi/vendor/kasi-package/migrations/2021_04_23_110457_create_foo.php');
        $this->runCommand($command, ['name' => 'create_foo', '--path' => 'vendor/kasi-package/migrations', '--create' => 'users']);
    }

    protected function runCommand($command, $input = [])
    {
        return $command->run(new ArrayInput($input), new NullOutput);
    }
}
