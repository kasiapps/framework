<?php

namespace Kasi\Tests\Integration\Database\SqlServer;

use Kasi\Database\Eloquent\Model;
use Kasi\Database\Schema\Blueprint;
use Kasi\Support\Facades\DB;
use Kasi\Support\Facades\Schema;

class DatabaseEloquentSqlServerIntegrationTest extends SqlServerTestCase
{
    protected function afterRefreshingDatabase()
    {
        if (! Schema::hasTable('database_eloquent_sql_server_integration_users')) {
            Schema::create('database_eloquent_sql_server_integration_users', function (Blueprint $table) {
                $table->id();
                $table->string('name')->nullable();
                $table->string('email')->unique();
                $table->timestamps();
            });
        }
    }

    protected function destroyDatabaseMigrations()
    {
        Schema::drop('database_eloquent_sql_server_integration_users');
    }

    public function testCreateOrFirst()
    {
        $user1 = DatabaseEloquentSqlServerIntegrationUser::createOrFirst(['email' => 'taylorotwell@gmail.com']);

        $this->assertSame('taylorotwell@gmail.com', $user1->email);
        $this->assertNull($user1->name);

        $user2 = DatabaseEloquentSqlServerIntegrationUser::createOrFirst(
            ['email' => 'taylorotwell@gmail.com'],
            ['name' => 'Taylor Otwell']
        );

        $this->assertEquals($user1->id, $user2->id);
        $this->assertSame('taylorotwell@gmail.com', $user2->email);
        $this->assertNull($user2->name);

        $user3 = DatabaseEloquentSqlServerIntegrationUser::createOrFirst(
            ['email' => 'abigailotwell@gmail.com'],
            ['name' => 'Abigail Otwell']
        );

        $this->assertNotEquals($user3->id, $user1->id);
        $this->assertSame('abigailotwell@gmail.com', $user3->email);
        $this->assertSame('Abigail Otwell', $user3->name);

        $user4 = DatabaseEloquentSqlServerIntegrationUser::createOrFirst(
            ['name' => 'Dries Vints'],
            ['name' => 'Nuno Maduro', 'email' => 'nuno@kasi.com']
        );

        $this->assertSame('Nuno Maduro', $user4->name);
    }

    public function testCreateOrFirstWithinTransaction()
    {
        $user1 = DatabaseEloquentSqlServerIntegrationUser::createOrFirst(['email' => 'taylor@kasi.com']);

        DB::transaction(function () use ($user1) {
            $user2 = DatabaseEloquentSqlServerIntegrationUser::createOrFirst(
                ['email' => 'taylor@kasi.com'],
                ['name' => 'Taylor Otwell']
            );

            $this->assertEquals($user1->id, $user2->id);
            $this->assertSame('taylor@kasi.com', $user2->email);
            $this->assertNull($user2->name);
        });
    }
}

class DatabaseEloquentSqlServerIntegrationUser extends Model
{
    protected $table = 'database_eloquent_sql_server_integration_users';

    protected $guarded = [];
}
