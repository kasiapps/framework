<?php

namespace Kasi\Tests\Integration\Generators;

use Kasi\Queue\Console\TableCommand;

class QueueTableCommandTest extends TestCase
{
    public function testCreateMakesMigration()
    {
        $this->artisan(TableCommand::class)->assertExitCode(0);

        $this->assertMigrationFileContains([
            'use Kasi\Database\Migrations\Migration;',
            'return new class extends Migration',
            'Schema::create(\'jobs\', function (Blueprint $table) {',
            'Schema::dropIfExists(\'jobs\');',
        ], 'create_jobs_table.php');
    }
}
