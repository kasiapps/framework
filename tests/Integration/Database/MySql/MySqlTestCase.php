<?php

namespace Kasi\Tests\Integration\Database\MySql;

use Kasi\Tests\Integration\Database\DatabaseTestCase;
use Orchestra\Testbench\Attributes\RequiresDatabase;

#[RequiresDatabase('mysql')]
abstract class MySqlTestCase extends DatabaseTestCase
{
    //
}
