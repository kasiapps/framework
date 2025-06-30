<?php

namespace Kasi\Tests\Integration\Generators;

use Kasi\Session\Console\SessionTableCommand;

class SessionTableCommandTest extends TestCase
{
    public function testCreateMakesMigration()
    {
        $this->artisan(SessionTableCommand::class)->assertExitCode(0);

        $this->assertMigrationFileContains([
            'use Kasi\Database\Migrations\Migration;',
            'return new class extends Migration',
            'Schema::create(\'sessions\', function (Blueprint $table) {',
            'Schema::dropIfExists(\'sessions\');',
        ], 'create_sessions_table.php');
    }
}
