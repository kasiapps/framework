<?php

namespace Kasi\Tests\Integration\Database\MariaDb;

use Kasi\Tests\Integration\Database\DatabaseTestCase;
use Orchestra\Testbench\Attributes\RequiresDatabase;

#[RequiresDatabase('mariadb')]
abstract class MariaDbTestCase extends DatabaseTestCase
{
    //
}
