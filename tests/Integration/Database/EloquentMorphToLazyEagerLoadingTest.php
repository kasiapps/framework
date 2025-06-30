<?php

namespace Kasi\Tests\Integration\Database\EloquentMorphToLazyEagerLoadingTest;

use DB;
use Kasi\Database\Eloquent\Model;
use Kasi\Database\Schema\Blueprint;
use Kasi\Support\Facades\Schema;
use Kasi\Tests\Integration\Database\DatabaseTestCase;

class EloquentMorphToLazyEagerLoadingTest extends DatabaseTestCase
{
    protected function afterRefreshingDatabase()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
        });

        Schema::create('posts', function (Blueprint $table) {
            $table->increments('post_id');
            $table->unsignedInteger('user_id');
        });

        Schema::create('videos', function (Blueprint $table) {
            $table->increments('video_id');
        });

        Schema::create('comments', function (Blueprint $table) {
            $table->increments('id');
            $table->string('commentable_type');
            $table->integer('commentable_id');
        });

        $user = User::create();

        $post = tap((new Post)->user()->associate($user))->save();

        $video = Video::create();

        (new Comment)->commentable()->associate($post)->save();
        (new Comment)->commentable()->associate($video)->save();
    }

    public function testLazyEagerLoading()
    {
        $comments = Comment::all();

        DB::enableQueryLog();

        $comments->load('commentable');

        $this->assertCount(3, DB::getQueryLog());
        $this->assertTrue($comments[0]->relationLoaded('commentable'));
        $this->assertTrue($comments[0]->commentable->relationLoaded('user'));
        $this->assertTrue($comments[1]->relationLoaded('commentable'));
    }
}

class Comment extends Model
{
    public $timestamps = false;

    public function commentable()
    {
        return $this->morphTo();
    }
}

class Post extends Model
{
    public $timestamps = false;
    protected $primaryKey = 'post_id';
    protected $with = ['user'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

class User extends Model
{
    public $timestamps = false;
}

class Video extends Model
{
    public $timestamps = false;
    protected $primaryKey = 'video_id';
}
