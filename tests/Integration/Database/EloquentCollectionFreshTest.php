<?php

namespace Kasi\Tests\Integration\Database;

use Kasi\Database\Eloquent\Collection as EloquentCollection;
use Kasi\Database\Schema\Blueprint;
use Kasi\Support\Facades\Schema;
use Kasi\Tests\Integration\Database\Fixtures\User;

class EloquentCollectionFreshTest extends DatabaseTestCase
{
    protected function afterRefreshingDatabase()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('email');
            $table->timestamps();
        });
    }

    public function testEloquentCollectionFresh()
    {
        User::insert([
            ['email' => 'kasi@framework.com'],
            ['email' => 'kasi@kasi.com'],
        ]);

        $collection = User::all();

        $collection->first()->delete();

        $freshCollection = $collection->fresh();

        $this->assertCount(1, $freshCollection);
        $this->assertInstanceOf(EloquentCollection::class, $freshCollection);
    }
}
