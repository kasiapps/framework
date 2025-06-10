<?php

declare(strict_types=1);

namespace Laravel\Lumen\Console;

use Illuminate\Auth\Console\ClearResetsCommand;
use Illuminate\Cache\Console\CacheTableCommand;
use Illuminate\Cache\Console\ClearCommand as CacheClearCommand;
use Illuminate\Cache\Console\ForgetCommand as CacheForgetCommand;
use Illuminate\Console\Scheduling\ScheduleFinishCommand;
use Illuminate\Console\Scheduling\ScheduleRunCommand;
use Illuminate\Console\Scheduling\ScheduleWorkCommand;
use Illuminate\Database\Console\DumpCommand;
use Illuminate\Database\Console\Migrations\FreshCommand as MigrateFreshCommand;
use Illuminate\Database\Console\Migrations\InstallCommand as MigrateInstallCommand;
use Illuminate\Database\Console\Migrations\MigrateCommand;
use Illuminate\Database\Console\Migrations\MigrateMakeCommand;
use Illuminate\Database\Console\Migrations\RefreshCommand as MigrateRefreshCommand;
use Illuminate\Database\Console\Migrations\ResetCommand as MigrateResetCommand;
use Illuminate\Database\Console\Migrations\RollbackCommand as MigrateRollbackCommand;
use Illuminate\Database\Console\Migrations\StatusCommand as MigrateStatusCommand;
use Illuminate\Database\Console\Seeds\SeedCommand;
use Illuminate\Database\Console\Seeds\SeederMakeCommand;
use Illuminate\Database\Console\WipeCommand;
use Illuminate\Queue\Console\BatchesTableCommand;
use Illuminate\Queue\Console\ClearCommand as ClearQueueCommand;
use Illuminate\Queue\Console\FailedTableCommand;
use Illuminate\Queue\Console\FlushFailedCommand as FlushFailedQueueCommand;
use Illuminate\Queue\Console\ForgetFailedCommand as ForgetFailedQueueCommand;
use Illuminate\Queue\Console\ListenCommand as QueueListenCommand;
use Illuminate\Queue\Console\ListFailedCommand as ListFailedQueueCommand;
use Illuminate\Queue\Console\RestartCommand as QueueRestartCommand;
use Illuminate\Queue\Console\RetryCommand as QueueRetryCommand;
use Illuminate\Queue\Console\TableCommand;
use Illuminate\Queue\Console\WorkCommand as QueueWorkCommand;
use Illuminate\Support\ServiceProvider;

class ConsoleServiceProvider extends ServiceProvider
{
  /**
   * The commands to be registered.
   *
   * @var array
   */
  protected $commands = [
    'CacheClear' => 'command.cache.clear',
    'CacheForget' => 'command.cache.forget',
    'ClearResets' => 'command.auth.resets.clear',
    'Migrate' => 'command.migrate',
    'MigrateInstall' => 'command.migrate.install',
    'MigrateFresh' => 'command.migrate.fresh',
    'MigrateRefresh' => 'command.migrate.refresh',
    'MigrateReset' => 'command.migrate.reset',
    'MigrateRollback' => 'command.migrate.rollback',
    'MigrateStatus' => 'command.migrate.status',
    'QueueClear' => 'command.queue.clear',
    'QueueFailed' => 'command.queue.failed',
    'QueueFlush' => 'command.queue.flush',
    'QueueForget' => 'command.queue.forget',
    'QueueListen' => 'command.queue.listen',
    'QueueRestart' => 'command.queue.restart',
    'QueueRetry' => 'command.queue.retry',
    'QueueWork' => 'command.queue.work',
    'Seed' => 'command.seed',
    'Wipe' => 'command.wipe',
    'ScheduleFinish' => 'command.schedule.finish',
    'ScheduleRun' => 'command.schedule.run',
    'ScheduleWork' => 'command.schedule.work',
    'SchemaDump' => 'command.schema.dump',
  ];

  /**
   * The commands to be registered.
   *
   * @var array
   */
  protected $devCommands = [
    'CacheTable' => 'command.cache.table',
    'MigrateMake' => 'command.migrate.make',
    'QueueFailedTable' => 'command.queue.failed-table',
    'QueueBatchesTable' => 'command.queue.batches-table',
    'QueueTable' => 'command.queue.table',
    'SeederMake' => 'command.seeder.make',
  ];

  /**
   * Register the service provider.
   */
  public function register(): void
  {
    $this->registerCommands(array_merge(
      $this->commands, $this->devCommands
    ));
  }

  /**
   * Register the given commands.
   *
   * @return void
   */
  protected function registerCommands(array $commands)
  {
    foreach (array_keys($commands) as $command) {
      $this->{"register{$command}Command"}();
    }

    $this->commands(array_values($commands));
  }

  /**
   * Register the command.
   *
   * @return void
   */
  protected function registerCacheClearCommand()
  {
    $this->app->singleton('command.cache.clear', fn ($app): CacheClearCommand => new CacheClearCommand($app['cache'], $app['files']));
  }

  /**
   * Register the command.
   *
   * @return void
   */
  protected function registerCacheForgetCommand()
  {
    $this->app->singleton('command.cache.forget', fn ($app): CacheForgetCommand => new CacheForgetCommand($app['cache']));
  }

  /**
   * Register the command.
   *
   * @return void
   */
  protected function registerCacheTableCommand()
  {
    $this->app->singleton('command.cache.table', fn ($app): CacheTableCommand => new CacheTableCommand($app['files'], $app['composer']));
  }

  /**
   * Register the command.
   *
   * @return void
   */
  protected function registerClearResetsCommand()
  {
    $this->app->singleton('command.auth.resets.clear', fn (): ClearResetsCommand => new ClearResetsCommand);
  }

  /**
   * Register the command.
   *
   * @return void
   */
  protected function registerMigrateCommand()
  {
    $this->app->singleton('command.migrate', fn ($app): MigrateCommand => new MigrateCommand($app['migrator'], $app['events']));
  }

  /**
   * Register the command.
   *
   * @return void
   */
  protected function registerMigrateInstallCommand()
  {
    $this->app->singleton('command.migrate.install', fn ($app): MigrateInstallCommand => new MigrateInstallCommand($app['migration.repository']));
  }

  /**
   * Register the command.
   *
   * @return void
   */
  protected function registerMigrateMakeCommand()
  {
    $this->app->singleton('command.migrate.make', function ($app): MigrateMakeCommand {
      // Once we have the migration creator registered, we will create the command
      // and inject the creator. The creator is responsible for the actual file
      // creation of the migrations, and may be extended by these developers.
      $creator = $app['migration.creator'];

      $composer = $app['composer'];

      return new MigrateMakeCommand($creator, $composer);
    });
  }

  /**
   * Register the command.
   *
   * @return void
   */
  protected function registerMigrateFreshCommand()
  {
    $this->app->singleton('command.migrate.fresh', fn ($app): MigrateFreshCommand => new MigrateFreshCommand($app['migrator']));
  }

  /**
   * Register the command.
   *
   * @return void
   */
  protected function registerMigrateRefreshCommand()
  {
    $this->app->singleton('command.migrate.refresh', fn (): MigrateRefreshCommand => new MigrateRefreshCommand);
  }

  /**
   * Register the command.
   *
   * @return void
   */
  protected function registerMigrateResetCommand()
  {
    $this->app->singleton('command.migrate.reset', fn ($app): MigrateResetCommand => new MigrateResetCommand($app['migrator']));
  }

  /**
   * Register the command.
   *
   * @return void
   */
  protected function registerMigrateRollbackCommand()
  {
    $this->app->singleton('command.migrate.rollback', fn ($app): MigrateRollbackCommand => new MigrateRollbackCommand($app['migrator']));
  }

  /**
   * Register the command.
   *
   * @return void
   */
  protected function registerMigrateStatusCommand()
  {
    $this->app->singleton('command.migrate.status', fn ($app): MigrateStatusCommand => new MigrateStatusCommand($app['migrator']));
  }

  /**
   * Register the command.
   *
   * @return void
   */
  protected function registerQueueClearCommand()
  {
    $this->app->singleton('command.queue.clear', fn (): \Illuminate\Queue\Console\ClearCommand => new ClearQueueCommand);
  }

  /**
   * Register the command.
   *
   * @return void
   */
  protected function registerQueueFailedCommand()
  {
    $this->app->singleton('command.queue.failed', fn (): ListFailedQueueCommand => new ListFailedQueueCommand);
  }

  /**
   * Register the command.
   *
   * @return void
   */
  protected function registerQueueForgetCommand()
  {
    $this->app->singleton('command.queue.forget', fn (): ForgetFailedQueueCommand => new ForgetFailedQueueCommand);
  }

  /**
   * Register the command.
   *
   * @return void
   */
  protected function registerQueueFlushCommand()
  {
    $this->app->singleton('command.queue.flush', fn (): FlushFailedQueueCommand => new FlushFailedQueueCommand);
  }

  /**
   * Register the command.
   *
   * @return void
   */
  protected function registerQueueListenCommand()
  {
    $this->app->singleton('command.queue.listen', fn ($app): QueueListenCommand => new QueueListenCommand($app['queue.listener']));
  }

  /**
   * Register the command.
   *
   * @return void
   */
  protected function registerQueueRestartCommand()
  {
    $this->app->singleton('command.queue.restart', fn ($app): QueueRestartCommand => new QueueRestartCommand($app['cache.store']));
  }

  /**
   * Register the command.
   *
   * @return void
   */
  protected function registerQueueRetryCommand()
  {
    $this->app->singleton('command.queue.retry', fn (): QueueRetryCommand => new QueueRetryCommand);
  }

  /**
   * Register the command.
   *
   * @return void
   */
  protected function registerQueueWorkCommand()
  {
    $this->app->singleton('command.queue.work', fn ($app): QueueWorkCommand => new QueueWorkCommand($app['queue.worker'], $app['cache.store']));
  }

  /**
   * Register the command.
   *
   * @return void
   */
  protected function registerQueueFailedTableCommand()
  {
    $this->app->singleton('command.queue.failed-table', fn ($app): FailedTableCommand => new FailedTableCommand($app['files'], $app['composer']));
  }

  /**
   * Register the command.
   *
   * @return void
   */
  protected function registerQueueBatchesTableCommand()
  {
    $this->app->singleton('command.queue.batches-table', fn ($app): BatchesTableCommand => new BatchesTableCommand($app['files'], $app['composer']));
  }

  /**
   * Register the command.
   *
   * @return void
   */
  protected function registerQueueTableCommand()
  {
    $this->app->singleton('command.queue.table', fn ($app): TableCommand => new TableCommand($app['files'], $app['composer']));
  }

  /**
   * Register the command.
   *
   * @return void
   */
  protected function registerSeederMakeCommand()
  {
    $this->app->singleton('command.seeder.make', fn ($app): SeederMakeCommand => new SeederMakeCommand($app['files'], $app['composer']));
  }

  /**
   * Register the command.
   *
   * @return void
   */
  protected function registerSeedCommand()
  {
    $this->app->singleton('command.seed', fn ($app): SeedCommand => new SeedCommand($app['db']));
  }

  /**
   * Register the command.
   *
   * @return void
   */
  protected function registerWipeCommand()
  {
    $this->app->singleton('command.wipe', fn ($app): WipeCommand => new WipeCommand($app['db']));
  }

  /**
   * Register the command.
   *
   * @return void
   */
  protected function registerScheduleFinishCommand()
  {
    $this->app->singleton('command.schedule.finish', fn (): ScheduleFinishCommand => new ScheduleFinishCommand);
  }

  /**
   * Register the command.
   *
   * @return void
   */
  protected function registerScheduleRunCommand()
  {
    $this->app->singleton('command.schedule.run', fn (): ScheduleRunCommand => new ScheduleRunCommand);
  }

  /**
   * Register the command.
   *
   * @return void
   */
  protected function registerScheduleWorkCommand()
  {
    $this->app->singleton('command.schedule.work', fn (): ScheduleWorkCommand => new ScheduleWorkCommand);
  }

  /**
   * Register the command.
   *
   * @return void
   */
  protected function registerSchemaDumpCommand()
  {
    $this->app->singleton('command.schema.dump', fn (): DumpCommand => new DumpCommand);
  }

  /**
   * Get the services provided by the provider.
   *
   * @return array
   */
  public function provides()
  {
    return array_merge(array_values($this->commands), array_values($this->devCommands));
  }
}
