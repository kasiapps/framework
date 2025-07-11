<?php

use Kasi\Database\Migrations\Migration;
use Kasi\Database\Schema\Blueprint;
use Kasi\Support\Facades\DB;
use Kasi\Support\Facades\Schema;

class CreatePeopleNonDynamicTable extends Migration
{
    public function up()
    {
        Schema::create('people', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });

        DB::table('people')->insert([
            ['email' => 'jane@example.com', 'name' => 'Jane Doe', 'password' => 'secret'],
            ['email' => 'john@example.com', 'name' => 'John Doe', 'password' => 'secret'],
        ]);
    }
}
