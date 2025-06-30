<?php

namespace Kasi\Tests\Integration\Database\SqlServer;

use Kasi\Tests\Integration\Database\DatabaseTestCase;
use Orchestra\Testbench\Attributes\RequiresDatabase;

#[RequiresDatabase('sqlsrv')]
abstract class SqlServerTestCase extends DatabaseTestCase
{
    //
}
