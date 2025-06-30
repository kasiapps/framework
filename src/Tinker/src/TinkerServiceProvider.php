<?php

declare(strict_types=1);

namespace Kasi\Tinker;

use Kasi\Contracts\Support\DeferrableProvider;
use Kasi\Support\ServiceProvider;
use Kasi\Tinker\Console\TinkerCommand;
use Override;

class TinkerServiceProvider extends ServiceProvider implements DeferrableProvider
{
  /**
   * Boot the service provider.
   */
  public function boot(): void
  {
    $source = realpath($raw = __DIR__.'/../config/tinker.php') ?: $raw;

    $this->app->configure('tinker');

    $this->mergeConfigFrom($source, 'tinker');
  }

  /**
   * Register the service provider.
   */
  #[Override]
  public function register(): void
  {
    $this->app->singleton('command.tinker', fn (): TinkerCommand => new TinkerCommand);

    $this->commands(['command.tinker']);
  }

  /**
   * Get the services provided by the provider.
   */
  #[Override]
  public function provides(): array
  {
    return ['command.tinker'];
  }
}
