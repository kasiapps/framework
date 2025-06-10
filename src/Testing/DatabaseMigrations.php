<?php

declare(strict_types=1);

namespace Laravel\Lumen\Testing;

trait DatabaseMigrations
{
  /**
   * Run the database migrations for the application.
   */
  public function runDatabaseMigrations(): void
  {
    $this->artisan('migrate:fresh');

    $this->beforeApplicationDestroyed(function (): void {
      $this->artisan('migrate:rollback');
    });
  }
}
