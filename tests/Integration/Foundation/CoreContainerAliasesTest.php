<?php

namespace Kasi\Tests\Integration\Foundation;

use Kasi\Database\ConnectionResolverInterface;
use Kasi\Database\DatabaseManager;
use Orchestra\Testbench\TestCase;

class CoreContainerAliasesTest extends TestCase
{
    public function testItCanResolveCoreContainerAliases()
    {
        $this->assertInstanceOf(DatabaseManager::class, $this->app->make(ConnectionResolverInterface::class));
    }
}
