<?php

declare(strict_types=1);

namespace Laravel\Lumen\Providers;

use Illuminate\Support\ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
  /**
   * The event handler mappings for the application.
   *
   * @var array
   */
  protected $listen = [];

  /**
   * The subscriber classes to register.
   *
   * @var array
   */
  protected $subscribe = [];

  /**
   * {@inheritdoc}
   */
  public function register(): void
  {
    //
  }

  /**
   * Register the application's event listeners.
   */
  public function boot(): void
  {
    $events = app('events');

    foreach ($this->listen as $event => $listeners) {
      foreach ($listeners as $listener) {
        $events->listen($event, $listener);
      }
    }

    foreach ($this->subscribe as $subscriber) {
      $events->subscribe($subscriber);
    }
  }

  /**
   * Get the events and handlers.
   *
   * @return array
   */
  public function listens()
  {
    return $this->listen;
  }
}
