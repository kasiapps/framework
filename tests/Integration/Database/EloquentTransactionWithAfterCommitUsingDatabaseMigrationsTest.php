<?php

namespace Kasi\Tests\Integration\Database;

use Kasi\Foundation\Testing\DatabaseMigrations;

class EloquentTransactionWithAfterCommitUsingDatabaseMigrationsTest extends DatabaseTestCase
{
    use EloquentTransactionWithAfterCommitTests;
    use DatabaseMigrations;
}
