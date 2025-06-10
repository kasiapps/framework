<?php

declare(strict_types=1);

namespace Laravel\Lumen\Testing;

trait DatabaseTransactions
{
  /**
   * Handle database transactions on the specified connections.
   */
  public function beginDatabaseTransaction(): void
  {
    $database = $this->app->make('db');

    foreach ($this->connectionsToTransact() as $name) {
      $database->connection($name)->beginTransaction();
    }

    $this->beforeApplicationDestroyed(function () use ($database): void {
      foreach ($this->connectionsToTransact() as $name) {
        $connection = $database->connection($name);

        $connection->rollBack();
        $connection->disconnect();
      }
    });
  }

  /**
   * The database connections that should have transactions.
   *
   * @return array
   */
  protected function connectionsToTransact()
  {
    return property_exists($this, 'connectionsToTransact')
        ? $this->connectionsToTransact : [null];
  }
}
