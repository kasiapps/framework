<?php

namespace Kasi\Tests\Integration\Database\Postgres;

use Kasi\Tests\Integration\Database\DatabaseTestCase;
use Orchestra\Testbench\Attributes\RequiresDatabase;

#[RequiresDatabase('pgsql')]
abstract class PostgresTestCase extends DatabaseTestCase
{
    //
}
