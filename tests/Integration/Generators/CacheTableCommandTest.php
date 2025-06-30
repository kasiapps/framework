<?php

namespace Kasi\Tests\Integration\Generators;

use Kasi\Cache\Console\CacheTableCommand;

class CacheTableCommandTest extends TestCase
{
    public function testCreateMakesMigration()
    {
        $this->artisan(CacheTableCommand::class)->assertExitCode(0);

        $this->assertMigrationFileContains([
            'use Kasi\Database\Migrations\Migration;',
            'return new class extends Migration',
            'Schema::create(\'cache\', function (Blueprint $table) {',
            'Schema::create(\'cache_locks\', function (Blueprint $table) {',
            'Schema::dropIfExists(\'cache\');',
            'Schema::dropIfExists(\'cache_locks\');',
        ], 'create_cache_table.php');
    }
}
