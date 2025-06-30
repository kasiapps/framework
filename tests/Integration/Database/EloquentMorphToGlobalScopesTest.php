<?php

namespace Kasi\Tests\Integration\Database\EloquentMorphToGlobalScopesTest;

use Kasi\Database\Eloquent\Model;
use Kasi\Database\Eloquent\SoftDeletes;
use Kasi\Database\Eloquent\SoftDeletingScope;
use Kasi\Database\Schema\Blueprint;
use Kasi\Support\Facades\Schema;
use Kasi\Tests\Integration\Database\DatabaseTestCase;

class EloquentMorphToGlobalScopesTest extends DatabaseTestCase
{
    protected function afterRefreshingDatabase()
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->increments('id');
            $table->softDeletes();
        });

        Schema::create('comments', function (Blueprint $table) {
            $table->increments('id');
            $table->string('commentable_type');
            $table->integer('commentable_id');
        });

        $post = Post::create();
        (new Comment)->commentable()->associate($post)->save();

        $post = tap(Post::create())->delete();
        (new Comment)->commentable()->associate($post)->save();
    }

    public function testWithGlobalScopes()
    {
        $comments = Comment::with('commentable')->get();

        $this->assertNotNull($comments[0]->commentable);
        $this->assertNull($comments[1]->commentable);
    }

    public function testWithoutGlobalScope()
    {
        $comments = Comment::with(['commentable' => function ($query) {
            $query->withoutGlobalScopes([SoftDeletingScope::class]);
        }])->get();

        $this->assertNotNull($comments[0]->commentable);
        $this->assertNotNull($comments[1]->commentable);
    }

    public function testWithoutGlobalScopes()
    {
        $comments = Comment::with(['commentable' => function ($query) {
            $query->withoutGlobalScopes();
        }])->get();

        $this->assertNotNull($comments[0]->commentable);
        $this->assertNotNull($comments[1]->commentable);
    }

    public function testLazyLoading()
    {
        $comment = Comment::latest('id')->first();
        $post = $comment->commentable()->withoutGlobalScopes()->first();

        $this->assertNotNull($post);
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
    use SoftDeletes;

    public $timestamps = false;
}
