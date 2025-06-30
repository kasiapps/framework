<?php

namespace Kasi\Tests\Integration\Database\EloquentTouchParentWithGlobalScopeTest;

use Kasi\Database\Eloquent\Model;
use Kasi\Database\Schema\Blueprint;
use Kasi\Support\Facades\Schema;
use Kasi\Support\Str;
use Kasi\Tests\Integration\Database\DatabaseTestCase;

class EloquentTouchParentWithGlobalScopeTest extends DatabaseTestCase
{
    protected function afterRefreshingDatabase()
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title');
            $table->timestamps();
        });

        Schema::create('comments', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('post_id');
            $table->string('title');
            $table->timestamps();
        });
    }

    public function testBasicCreateAndRetrieve()
    {
        $post = Post::create(['title' => Str::random(), 'updated_at' => '2016-10-10 10:10:10']);

        $this->assertSame('2016-10-10', $post->fresh()->updated_at->toDateString());

        $post->comments()->create(['title' => Str::random()]);

        $this->assertNotSame('2016-10-10', $post->fresh()->updated_at->toDateString());
    }
}

class Post extends Model
{
    public $table = 'posts';
    public $timestamps = true;
    protected $guarded = [];

    public function comments()
    {
        return $this->hasMany(Comment::class, 'post_id');
    }

    public static function boot()
    {
        parent::boot();

        static::addGlobalScope('age', function ($builder) {
            $builder->join('comments', 'comments.post_id', '=', 'posts.id');
        });
    }
}

class Comment extends Model
{
    public $table = 'comments';
    public $timestamps = true;
    protected $guarded = [];
    protected $touches = ['post'];

    public function post()
    {
        return $this->belongsTo(Post::class, 'post_id');
    }
}
