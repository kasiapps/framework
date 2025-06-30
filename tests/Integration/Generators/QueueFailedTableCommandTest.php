<?php

namespace Kasi\Tests\Integration\Generators;

use Kasi\Queue\Console\FailedTableCommand;

class QueueFailedTableCommandTest extends TestCase
{
    public function testCreateMakesMigration()
    {
        $this->artisan(FailedTableCommand::class)->assertExitCode(0);

        $this->assertMigrationFileContains([
            'use Kasi\Database\Migrations\Migration;',
            'return new class extends Migration',
            'Schema::create(\'failed_jobs\', function (Blueprint $table) {',
            'Schema::dropIfExists(\'failed_jobs\');',
        ], 'create_failed_jobs_table.php');
    }
}
