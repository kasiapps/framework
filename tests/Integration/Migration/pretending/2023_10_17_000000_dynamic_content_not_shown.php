<?php

use Kasi\Database\Migrations\Migration;
use Kasi\Database\Schema\Blueprint;
use Kasi\Support\Facades\DB;
use Kasi\Support\Facades\Schema;

class DynamicContentNotShown extends Migration
{
    public function up()
    {
        Schema::create('blogs', function (Blueprint $table) {
            $table->increments('id');
            $table->string('url')->nullable();
            $table->string('name')->nullable();
        });

        DB::table('blogs')->insert([
            ['url' => 'www.janedoe.com'],
            ['url' => 'www.johndoe.com'],
        ]);

        DB::statement("ALTER TABLE 'pseudo_table_name' MODIFY 'column_name' VARCHAR(191)");

        DB::table('people')->get()->each(function ($person, $key) {
            DB::table('blogs')->where('blog_id', '=', $person->blog_id)->insert([
                'id' => $key + 1,
                'name' => "{$person->name} Blog",
            ]);
        });
    }
}
