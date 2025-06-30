<?php

namespace App\Integration\Database;

use Kasi\Database\Eloquent\Collection;
use Kasi\Database\Eloquent\Model;
use Kasi\Database\Eloquent\SoftDeletes;
use Kasi\Database\Schema\Blueprint;
use Kasi\Support\Facades\DB;
use Kasi\Support\Facades\Schema;
use Kasi\Tests\Integration\Database\DatabaseTestCase;

class EloquentCollectionLoadCountTest extends DatabaseTestCase
{
    protected function afterRefreshingDatabase()
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('some_default_value');
            $table->softDeletes();
        });

        Schema::create('comments', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('post_id');
        });

        Schema::create('likes', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('post_id');
        });

        $post = Post::create();
        $post->comments()->saveMany([new Comment, new Comment]);

        $post->likes()->save(new Like);

        Post::create();
    }

    public function testLoadCount()
    {
        $posts = Post::all();

        DB::enableQueryLog();

        $posts->loadCount('comments');

        $this->assertCount(1, DB::getQueryLog());
        $this->assertSame('2', (string) $posts[0]->comments_count);
        $this->assertSame('0', (string) $posts[1]->comments_count);
        $this->assertSame('2', (string) $posts[0]->getOriginal('comments_count'));
    }

    public function testLoadCountWithSameModels()
    {
        $posts = Post::all()->push(Post::first());

        DB::enableQueryLog();

        $posts->loadCount('comments');

        $this->assertCount(1, DB::getQueryLog());
        $this->assertSame('2', (string) $posts[0]->comments_count);
        $this->assertSame('0', (string) $posts[1]->comments_count);
        $this->assertSame('2', (string) $posts[2]->comments_count);
    }

    public function testLoadCountOnDeletedModels()
    {
        $posts = Post::all()->each->delete();

        DB::enableQueryLog();

        $posts->loadCount('comments');

        $this->assertCount(1, DB::getQueryLog());
        $this->assertSame('2', (string) $posts[0]->comments_count);
        $this->assertSame('0', (string) $posts[1]->comments_count);
    }

    public function testLoadCountWithArrayOfRelations()
    {
        $posts = Post::all();

        DB::enableQueryLog();

        $posts->loadCount(['comments', 'likes']);

        $this->assertCount(1, DB::getQueryLog());
        $this->assertSame('2', (string) $posts[0]->comments_count);
        $this->assertSame('1', (string) $posts[0]->likes_count);
        $this->assertSame('0', (string) $posts[1]->comments_count);
        $this->assertSame('0', (string) $posts[1]->likes_count);
    }

    public function testLoadCountDoesNotOverrideAttributesWithDefaultValue()
    {
        $post = Post::first();
        $post->some_default_value = 200;

        Collection::make([$post])->loadCount('comments');

        $this->assertSame(200, $post->some_default_value);
        $this->assertSame('2', (string) $post->comments_count);
    }
}

class Post extends Model
{
    use SoftDeletes;

    protected $attributes = [
        'some_default_value' => 100,
    ];

    public $timestamps = false;

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function likes()
    {
        return $this->hasMany(Like::class);
    }
}

class Comment extends Model
{
    public $timestamps = false;
}

class Like extends Model
{
    public $timestamps = false;
}
