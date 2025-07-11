<?php

namespace Kasi\Tests\Integration\Database\EloquentWhereHasTest;

use Kasi\Database\Eloquent\Builder as EloquentBuilder;
use Kasi\Database\Eloquent\Model;
use Kasi\Database\Query\Builder as QueryBuilder;
use Kasi\Database\Schema\Blueprint;
use Kasi\Support\Facades\DB;
use Kasi\Support\Facades\Schema;
use Kasi\Tests\Integration\Database\DatabaseTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class EloquentWhereHasTest extends DatabaseTestCase
{
    protected function afterRefreshingDatabase()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
        });

        Schema::create('posts', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->boolean('public');
        });

        Schema::create('texts', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('post_id');
            $table->text('content');
        });

        Schema::create('comments', function (Blueprint $table) {
            $table->increments('id');
            $table->string('commentable_type');
            $table->integer('commentable_id');
        });

        $user = User::create();
        $post = tap((new Post(['public' => true]))->user()->associate($user))->save();
        (new Comment)->commentable()->associate($post)->save();
        (new Text(['content' => 'test']))->post()->associate($post)->save();

        $user = User::create();
        $post = tap((new Post(['public' => false]))->user()->associate($user))->save();
        (new Comment)->commentable()->associate($post)->save();
        (new Text(['content' => 'test2']))->post()->associate($post)->save();
    }

    /**
     * Check that the 'whereRelation' callback function works.
     */
    #[DataProvider('dataProviderWhereRelationCallback')]
    public function testWhereRelationCallback($callbackEloquent, $callbackQuery)
    {
        $userWhereRelation = User::whereRelation('posts', $callbackEloquent);
        $userWhereHas = User::whereHas('posts', $callbackEloquent);
        $query = DB::table('users')->whereExists($callbackQuery);

        $this->assertEquals($userWhereRelation->getQuery()->toSql(), $query->toSql());
        $this->assertEquals($userWhereRelation->getQuery()->toSql(), $userWhereHas->toSql());
        $this->assertEquals($userWhereHas->getQuery()->toSql(), $query->toSql());

        $this->assertEquals($userWhereRelation->first()->id, $query->first()->id);
        $this->assertEquals($userWhereRelation->first()->id, $userWhereHas->first()->id);
        $this->assertEquals($userWhereHas->first()->id, $query->first()->id);
    }

    /**
     * Check that the 'orWhereRelation' callback function works.
     */
    #[DataProvider('dataProviderWhereRelationCallback')]
    public function testOrWhereRelationCallback($callbackEloquent, $callbackQuery)
    {
        $userOrWhereRelation = User::orWhereRelation('posts', $callbackEloquent);
        $userOrWhereHas = User::orWhereHas('posts', $callbackEloquent);
        $query = DB::table('users')->orWhereExists($callbackQuery);

        $this->assertEquals($userOrWhereRelation->getQuery()->toSql(), $query->toSql());
        $this->assertEquals($userOrWhereRelation->getQuery()->toSql(), $userOrWhereHas->toSql());
        $this->assertEquals($userOrWhereHas->getQuery()->toSql(), $query->toSql());

        $this->assertEquals($userOrWhereRelation->first()->id, $query->first()->id);
        $this->assertEquals($userOrWhereRelation->first()->id, $userOrWhereHas->first()->id);
        $this->assertEquals($userOrWhereHas->first()->id, $query->first()->id);
    }

    /**
     * Check that the 'whereDoesntHaveRelation' callback function works.
     */
    #[DataProvider('dataProviderWhereRelationCallback')]
    public function testWhereDoesntRelationCallback($callbackEloquent, $callbackQuery)
    {
        $userWhereDoesntRelation = User::whereDoesntHaveRelation('posts', $callbackEloquent);
        $userWhereHas = User::whereDoesntHave('posts', $callbackEloquent);
        $query = DB::table('users')->whereNotExists($callbackQuery);

        $this->assertEquals($userWhereDoesntRelation->getQuery()->toSql(), $query->toSql());
        $this->assertEquals($userWhereDoesntRelation->getQuery()->toSql(), $userWhereHas->toSql());
        $this->assertEquals($userWhereHas->getQuery()->toSql(), $query->toSql());

        $this->assertEquals($userWhereDoesntRelation->first()->id, $query->first()->id);
        $this->assertEquals($userWhereDoesntRelation->first()->id, $userWhereHas->first()->id);
        $this->assertEquals($userWhereHas->first()->id, $query->first()->id);
    }

    /**
     * Check that the 'orWhereDoesntRelation' callback function works.
     */
    #[DataProvider('dataProviderWhereRelationCallback')]
    public function testOrWhereDoesntRelationCallback($callbackEloquent, $callbackQuery)
    {
        $userOrWhereDoesntRelation = User::orWhereDoesntHaveRelation('posts', $callbackEloquent);
        $userOrWhereHas = User::orWhereDoesntHave('posts', $callbackEloquent);
        $query = DB::table('users')->orWhereNotExists($callbackQuery);

        $this->assertEquals($userOrWhereDoesntRelation->getQuery()->toSql(), $query->toSql());
        $this->assertEquals($userOrWhereDoesntRelation->getQuery()->toSql(), $userOrWhereHas->toSql());
        $this->assertEquals($userOrWhereHas->getQuery()->toSql(), $query->toSql());

        $this->assertEquals($userOrWhereDoesntRelation->first()->id, $query->first()->id);
        $this->assertEquals($userOrWhereDoesntRelation->first()->id, $userOrWhereHas->first()->id);
        $this->assertEquals($userOrWhereHas->first()->id, $query->first()->id);
    }

    public static function dataProviderWhereRelationCallback()
    {
        $callbackArray = function ($value) {
            $callbackEloquent = function (EloquentBuilder $builder) use ($value) {
                $builder->selectRaw('id')->where('public', $value);
            };

            $callbackQuery = function (QueryBuilder $builder) use ($value) {
                $hasMany = app()->make(User::class)->posts();

                $builder->from('posts')->addSelect(['*'])->whereColumn(
                    $hasMany->getQualifiedParentKeyName(),
                    '=',
                    $hasMany->getQualifiedForeignKeyName()
                );

                $builder->selectRaw('id')->where('public', $value);
            };

            return [$callbackEloquent, $callbackQuery];
        };

        return [
            'Find user with post.public = true' => $callbackArray(true),
            'Find user with post.public = false' => $callbackArray(false),
        ];
    }

    public function testWhereRelation()
    {
        $users = User::whereRelation('posts', 'public', true)->get();

        $this->assertEquals([1], $users->pluck('id')->all());
    }

    public function testOrWhereRelation()
    {
        $users = User::whereRelation('posts', 'public', true)->orWhereRelation('posts', 'public', false)->get();

        $this->assertEquals([1, 2], $users->pluck('id')->all());
    }

    public function testNestedWhereRelation()
    {
        $texts = User::whereRelation('posts.texts', 'content', 'test')->get();

        $this->assertEquals([1], $texts->pluck('id')->all());
    }

    public function testNestedOrWhereRelation()
    {
        $texts = User::whereRelation('posts.texts', 'content', 'test')->orWhereRelation('posts.texts', 'content', 'test2')->get();

        $this->assertEquals([1, 2], $texts->pluck('id')->all());
    }

    public function testWhereMorphRelation()
    {
        $comments = Comment::whereMorphRelation('commentable', '*', 'public', true)->get();

        $this->assertEquals([1], $comments->pluck('id')->all());
    }

    public function testOrWhereMorphRelation()
    {
        $comments = Comment::whereMorphRelation('commentable', '*', 'public', true)
            ->orWhereMorphRelation('commentable', '*', 'public', false)
            ->get();

        $this->assertEquals([1, 2], $comments->pluck('id')->all());
    }

    public function testWhereDoesntHaveRelation()
    {
        $users = User::whereDoesntHaveRelation('posts', 'public', true)->get();

        $this->assertEquals([2], $users->pluck('id')->all());
    }

    public function testOrWhereDoesntHaveRelation()
    {
        $users = User::whereDoesntHaveRelation('posts', 'public', true)->orWhereDoesntHaveRelation('posts', 'public', false)->get();

        $this->assertEquals([1, 2], $users->pluck('id')->all());
    }

    public function testNestedWhereDoesntHaveRelation()
    {
        $texts = User::whereDoesntHaveRelation('posts.texts', 'content', 'test')->get();

        $this->assertEquals([2], $texts->pluck('id')->all());
    }

    public function testNestedOrWhereDoesntHaveRelation()
    {
        $texts = User::whereDoesntHaveRelation('posts.texts', 'content', 'test')->orWhereDoesntHaveRelation('posts.texts', 'content', 'test2')->get();

        $this->assertEquals([1, 2], $texts->pluck('id')->all());
    }

    public function testWhereMorphDoesntHaveRelation()
    {
        $comments = Comment::whereMorphDoesntHaveRelation('commentable', '*', 'public', true)->get();

        $this->assertEquals([2], $comments->pluck('id')->all());
    }

    public function testOrWhereMorphDoesntHaveRelation()
    {
        $comments = Comment::whereMorphDoesntHaveRelation('commentable', '*', 'public', true)
            ->orWhereMorphDoesntHaveRelation('commentable', '*', 'public', false)
            ->get();

        $this->assertEquals([1, 2], $comments->pluck('id')->all());
    }

    public function testWithCount()
    {
        $users = User::whereHas('posts', function ($query) {
            $query->where('public', true);
        })->get();

        $this->assertEquals([1], $users->pluck('id')->all());
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

    protected $guarded = [];

    protected $withCount = ['comments'];

    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function texts()
    {
        return $this->hasMany(Text::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

class Text extends Model
{
    public $timestamps = false;

    protected $guarded = [];

    public function post()
    {
        return $this->belongsTo(Post::class);
    }
}

class User extends Model
{
    public $timestamps = false;

    public function posts()
    {
        return $this->hasMany(Post::class);
    }
}
