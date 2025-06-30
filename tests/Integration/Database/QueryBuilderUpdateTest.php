<?php

namespace Kasi\Tests\Integration\Database;

use Kasi\Database\Schema\Blueprint;
use Kasi\Support\Facades\DB;
use Kasi\Support\Facades\Schema;
use Orchestra\Testbench\Attributes\RequiresDatabase;
use PHPUnit\Framework\Attributes\DataProvider;

require_once 'Enums.php';

class QueryBuilderUpdateTest extends DatabaseTestCase
{
    protected function afterRefreshingDatabase()
    {
        Schema::create('example', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->nullable();
            $table->string('title')->nullable();
            $table->string('status')->nullable();
            $table->json('payload')->nullable();
        });
    }

    #[DataProvider('jsonValuesDataProvider')]
    #[RequiresDatabase(['sqlite', 'mysql', 'mariadb'])]
    public function testBasicUpdateForJson($column, $given, $expected)
    {
        DB::table('example')->insert([
            'name' => 'Taylor Otwell',
            'title' => 'Mr.',
        ]);

        DB::table('example')->update([$column => $given]);

        $this->assertDatabaseHas('example', [
            'name' => 'Taylor Otwell',
            'title' => 'Mr.',
            $column => $column === 'payload' ? $this->castAsJson($expected) : $expected,
        ]);
    }

    public static function jsonValuesDataProvider()
    {
        yield ['payload', ['Kasi', 'Founder'], ['Kasi', 'Founder']];
        yield ['payload', collect(['Kasi', 'Founder']), ['Kasi', 'Founder']];
        yield ['status', StringStatus::draft, 'draft'];
    }
}
