<?php

namespace Kasi\Types\Builder;

use Kasi\Database\Eloquent\Builder;
use Kasi\Database\Eloquent\HasBuilder;
use Kasi\Database\Eloquent\Model;
use Kasi\Database\Eloquent\Relations\BelongsTo;
use Kasi\Database\Eloquent\Relations\HasMany;
use Kasi\Database\Eloquent\Relations\MorphTo;
use Kasi\Database\Query\Builder as QueryBuilder;

use function PHPStan\Testing\assertType;

/** @param \Kasi\Database\Eloquent\Builder<User> $query */
function test(
    Builder $query,
    User $user,
    Post $post,
    ChildPost $childPost,
    Comment $comment,
    QueryBuilder $queryBuilder
): void {
    assertType('Kasi\Database\Eloquent\Builder<Kasi\Types\Builder\User>', $query->where('id', 1));
    assertType('Kasi\Database\Eloquent\Builder<Kasi\Types\Builder\User>', $query->orWhere('name', 'John'));
    assertType('Kasi\Database\Eloquent\Builder<Kasi\Types\Builder\User>', $query->whereNot('status', 'active'));
    assertType('Kasi\Database\Eloquent\Builder<Kasi\Types\Builder\User>', $query->with('relation'));
    assertType('Kasi\Database\Eloquent\Builder<Kasi\Types\Builder\User>', $query->with(['relation' => ['foo' => fn ($q) => $q]]));
    assertType('Kasi\Database\Eloquent\Builder<Kasi\Types\Builder\User>', $query->with(['relation' => function ($query) {
        // assertType('Kasi\Database\Eloquent\Relations\Relation<*,*,*>', $query);
    }]));
    assertType('Kasi\Database\Eloquent\Builder<Kasi\Types\Builder\User>', $query->without('relation'));
    assertType('Kasi\Database\Eloquent\Builder<Kasi\Types\Builder\User>', $query->withOnly(['relation']));
    assertType('Kasi\Database\Eloquent\Builder<Kasi\Types\Builder\User>', $query->withOnly(['relation' => ['foo' => fn ($q) => $q]]));
    assertType('Kasi\Database\Eloquent\Builder<Kasi\Types\Builder\User>', $query->withOnly(['relation' => function ($query) {
        // assertType('Kasi\Database\Eloquent\Relations\Relation<*,*,*>', $query);
    }]));
    assertType('array<int, Kasi\Types\Builder\User>', $query->getModels());
    assertType('array<int, Kasi\Types\Builder\User>', $query->eagerLoadRelations([]));
    assertType('Kasi\Database\Eloquent\Collection<int, Kasi\Types\Builder\User>', $query->get());
    assertType('Kasi\Database\Eloquent\Collection<int, Kasi\Types\Builder\User>', $query->hydrate([]));
    assertType('Kasi\Database\Eloquent\Collection<int, Kasi\Types\Builder\User>', $query->fromQuery('foo', []));
    assertType('Kasi\Database\Eloquent\Collection<int, Kasi\Types\Builder\User>', $query->findMany([1, 2, 3]));
    assertType('Kasi\Database\Eloquent\Collection<int, Kasi\Types\Builder\User>', $query->findOrFail([1]));
    assertType('Kasi\Database\Eloquent\Collection<int, Kasi\Types\Builder\User>', $query->findOrNew([1]));
    assertType('Kasi\Database\Eloquent\Collection<int, Kasi\Types\Builder\User>', $query->find([1]));
    assertType('Kasi\Database\Eloquent\Collection<int, Kasi\Types\Builder\User>', $query->findOr([1], callback: fn () => 42));
    assertType('Kasi\Types\Builder\User', $query->findOrFail(1));
    assertType('Kasi\Types\Builder\User|null', $query->find(1));
    assertType('42|Kasi\Types\Builder\User', $query->findOr(1, fn () => 42));
    assertType('42|Kasi\Types\Builder\User', $query->findOr(1, callback: fn () => 42));
    assertType('Kasi\Types\Builder\User|null', $query->first());
    assertType('42|Kasi\Types\Builder\User', $query->firstOr(fn () => 42));
    assertType('42|Kasi\Types\Builder\User', $query->firstOr(callback: fn () => 42));
    assertType('Kasi\Types\Builder\User', $query->firstOrNew(['id' => 1]));
    assertType('Kasi\Types\Builder\User', $query->findOrNew(1));
    assertType('Kasi\Types\Builder\User', $query->firstOrCreate(['id' => 1]));
    assertType('Kasi\Types\Builder\User', $query->create(['name' => 'John']));
    assertType('Kasi\Types\Builder\User', $query->forceCreate(['name' => 'John']));
    assertType('Kasi\Types\Builder\User', $query->forceCreateQuietly(['name' => 'John']));
    assertType('Kasi\Types\Builder\User', $query->getModel());
    assertType('Kasi\Types\Builder\User', $query->make(['name' => 'John']));
    assertType('Kasi\Types\Builder\User', $query->forceCreate(['name' => 'John']));
    assertType('Kasi\Types\Builder\User', $query->updateOrCreate(['id' => 1], ['name' => 'John']));
    assertType('Kasi\Types\Builder\User', $query->firstOrFail());
    assertType('Kasi\Types\Builder\User', $query->sole());
    assertType('Kasi\Support\LazyCollection<int, Kasi\Types\Builder\User>', $query->cursor());
    assertType('Kasi\Support\LazyCollection<int, Kasi\Types\Builder\User>', $query->cursor());
    assertType('Kasi\Support\LazyCollection<int, Kasi\Types\Builder\User>', $query->lazy());
    assertType('Kasi\Support\LazyCollection<int, Kasi\Types\Builder\User>', $query->lazyById());
    assertType('Kasi\Support\LazyCollection<int, Kasi\Types\Builder\User>', $query->lazyByIdDesc());
    assertType('Kasi\Support\Collection<(int|string), mixed>', $query->pluck('foo'));
    assertType('Kasi\Database\Eloquent\Relations\Relation<Kasi\Database\Eloquent\Model, Kasi\Types\Builder\User, *>', $query->getRelation('foo'));
    assertType('Kasi\Database\Eloquent\Builder<Kasi\Types\Builder\Post>', $query->setModel(new Post()));

    assertType('Kasi\Database\Eloquent\Builder<Kasi\Types\Builder\User>', $query->has('foo', callback: function ($query) {
        assertType('Kasi\Database\Eloquent\Builder<Kasi\Database\Eloquent\Model>', $query);
    }));
    assertType('Kasi\Database\Eloquent\Builder<Kasi\Types\Builder\User>', $query->has($user->posts(), callback: function ($query) {
        assertType('Kasi\Database\Eloquent\Builder<Kasi\Types\Builder\Post>', $query);
    }));
    assertType('Kasi\Database\Eloquent\Builder<Kasi\Types\Builder\User>', $query->orHas($user->posts()));
    assertType('Kasi\Database\Eloquent\Builder<Kasi\Types\Builder\User>', $query->doesntHave($user->posts(), callback: function ($query) {
        assertType('Kasi\Database\Eloquent\Builder<Kasi\Types\Builder\Post>', $query);
    }));
    assertType('Kasi\Database\Eloquent\Builder<Kasi\Types\Builder\User>', $query->orDoesntHave($user->posts()));
    assertType('Kasi\Database\Eloquent\Builder<Kasi\Types\Builder\User>', $query->whereHas($user->posts(), function ($query) {
        assertType('Kasi\Database\Eloquent\Builder<Kasi\Types\Builder\Post>', $query);
    }));
    assertType('Kasi\Database\Eloquent\Builder<Kasi\Types\Builder\User>', $query->withWhereHas('posts', function ($query) {
        assertType('Kasi\Database\Eloquent\Builder<*>|Kasi\Database\Eloquent\Relations\Relation<*, *, *>', $query);
    }));
    assertType('Kasi\Database\Eloquent\Builder<Kasi\Types\Builder\User>', $query->orWhereHas($user->posts(), function ($query) {
        assertType('Kasi\Database\Eloquent\Builder<Kasi\Types\Builder\Post>', $query);
    }));
    assertType('Kasi\Database\Eloquent\Builder<Kasi\Types\Builder\User>', $query->whereDoesntHave($user->posts(), function ($query) {
        assertType('Kasi\Database\Eloquent\Builder<Kasi\Types\Builder\Post>', $query);
    }));
    assertType('Kasi\Database\Eloquent\Builder<Kasi\Types\Builder\User>', $query->orWhereDoesntHave($user->posts(), function ($query) {
        assertType('Kasi\Database\Eloquent\Builder<Kasi\Types\Builder\Post>', $query);
    }));
    assertType('Kasi\Database\Eloquent\Builder<Kasi\Types\Builder\User>', $query->hasMorph($post->taggable(), 'taggable', callback: function ($query, $type) {
        assertType('Kasi\Database\Eloquent\Builder<Kasi\Database\Eloquent\Model>', $query);
        assertType('string', $type);
    }));
    assertType('Kasi\Database\Eloquent\Builder<Kasi\Types\Builder\User>', $query->orHasMorph($post->taggable(), 'taggable'));
    assertType('Kasi\Database\Eloquent\Builder<Kasi\Types\Builder\User>', $query->doesntHaveMorph($post->taggable(), 'taggable', callback: function ($query, $type) {
        assertType('Kasi\Database\Eloquent\Builder<Kasi\Database\Eloquent\Model>', $query);
        assertType('string', $type);
    }));
    assertType('Kasi\Database\Eloquent\Builder<Kasi\Types\Builder\User>', $query->orDoesntHaveMorph($post->taggable(), 'taggable'));
    assertType('Kasi\Database\Eloquent\Builder<Kasi\Types\Builder\User>', $query->whereHasMorph($post->taggable(), 'taggable', function ($query, $type) {
        assertType('Kasi\Database\Eloquent\Builder<Kasi\Database\Eloquent\Model>', $query);
        assertType('string', $type);
    }));
    assertType('Kasi\Database\Eloquent\Builder<Kasi\Types\Builder\User>', $query->orWhereHasMorph($post->taggable(), 'taggable', function ($query, $type) {
        assertType('Kasi\Database\Eloquent\Builder<Kasi\Database\Eloquent\Model>', $query);
        assertType('string', $type);
    }));
    assertType('Kasi\Database\Eloquent\Builder<Kasi\Types\Builder\User>', $query->whereDoesntHaveMorph($post->taggable(), 'taggable', function ($query, $type) {
        assertType('Kasi\Database\Eloquent\Builder<Kasi\Database\Eloquent\Model>', $query);
        assertType('string', $type);
    }));
    assertType('Kasi\Database\Eloquent\Builder<Kasi\Types\Builder\User>', $query->orWhereDoesntHaveMorph($post->taggable(), 'taggable', function ($query, $type) {
        assertType('Kasi\Database\Eloquent\Builder<Kasi\Database\Eloquent\Model>', $query);
        assertType('string', $type);
    }));
    assertType('Kasi\Database\Eloquent\Builder<Kasi\Types\Builder\User>', $query->whereRelation($user->posts(), function ($query) {
        assertType('Kasi\Database\Eloquent\Builder<Kasi\Types\Builder\Post>', $query);
    }));
    assertType('Kasi\Database\Eloquent\Builder<Kasi\Types\Builder\User>', $query->orWhereRelation($user->posts(), function ($query) {
        assertType('Kasi\Database\Eloquent\Builder<Kasi\Types\Builder\Post>', $query);
    }));
    assertType('Kasi\Database\Eloquent\Builder<Kasi\Types\Builder\User>', $query->whereDoesntHaveRelation($user->posts(), function ($query) {
        assertType('Kasi\Database\Eloquent\Builder<Kasi\Types\Builder\Post>', $query);
    }));
    assertType('Kasi\Database\Eloquent\Builder<Kasi\Types\Builder\User>', $query->orWhereDoesntHaveRelation($user->posts(), function ($query) {
        assertType('Kasi\Database\Eloquent\Builder<Kasi\Types\Builder\Post>', $query);
    }));
    assertType('Kasi\Database\Eloquent\Builder<Kasi\Types\Builder\User>', $query->whereMorphRelation($post->taggable(), 'taggable', function ($query) {
        assertType('Kasi\Database\Eloquent\Builder<Kasi\Database\Eloquent\Model>', $query);
    }));
    assertType('Kasi\Database\Eloquent\Builder<Kasi\Types\Builder\User>', $query->orWhereMorphRelation($post->taggable(), 'taggable', function ($query) {
        assertType('Kasi\Database\Eloquent\Builder<Kasi\Database\Eloquent\Model>', $query);
    }));
    assertType('Kasi\Database\Eloquent\Builder<Kasi\Types\Builder\User>', $query->whereMorphDoesntHaveRelation($post->taggable(), 'taggable', function ($query) {
        assertType('Kasi\Database\Eloquent\Builder<Kasi\Database\Eloquent\Model>', $query);
    }));
    assertType('Kasi\Database\Eloquent\Builder<Kasi\Types\Builder\User>', $query->orWhereMorphDoesntHaveRelation($post->taggable(), 'taggable', function ($query) {
        assertType('Kasi\Database\Eloquent\Builder<Kasi\Database\Eloquent\Model>', $query);
    }));
    assertType('Kasi\Database\Eloquent\Builder<Kasi\Types\Builder\User>', $query->whereMorphedTo($post->taggable(), new Post()));
    assertType('Kasi\Database\Eloquent\Builder<Kasi\Types\Builder\User>', $query->whereNotMorphedTo($post->taggable(), new Post()));
    assertType('Kasi\Database\Eloquent\Builder<Kasi\Types\Builder\User>', $query->orWhereMorphedTo($post->taggable(), new Post()));
    assertType('Kasi\Database\Eloquent\Builder<Kasi\Types\Builder\User>', $query->orWhereNotMorphedTo($post->taggable(), new Post()));

    $query->chunk(1, function ($users, $page) {
        assertType('Kasi\Support\Collection<int, Kasi\Types\Builder\User>', $users);
        assertType('int', $page);
    });
    $query->chunkById(1, function ($users, $page) {
        assertType('Kasi\Support\Collection<int, Kasi\Types\Builder\User>', $users);
        assertType('int', $page);
    });
    $query->chunkMap(function ($users) {
        assertType('Kasi\Types\Builder\User', $users);
    });
    $query->chunkByIdDesc(1, function ($users, $page) {
        assertType('Kasi\Support\Collection<int, Kasi\Types\Builder\User>', $users);
        assertType('int', $page);
    });
    $query->each(function ($users, $page) {
        assertType('Kasi\Types\Builder\User', $users);
        assertType('int', $page);
    });
    $query->eachById(function ($users, $page) {
        assertType('Kasi\Types\Builder\User', $users);
        assertType('int', $page);
    });

    assertType('Kasi\Types\Builder\CommonBuilder<Kasi\Types\Builder\Post>', Post::query());
    assertType('Kasi\Types\Builder\CommonBuilder<Kasi\Types\Builder\Post>', Post::on());
    assertType('Kasi\Types\Builder\CommonBuilder<Kasi\Types\Builder\Post>', Post::onWriteConnection());
    assertType('Kasi\Types\Builder\CommonBuilder<Kasi\Types\Builder\Post>', Post::with([]));
    assertType('Kasi\Types\Builder\CommonBuilder<Kasi\Types\Builder\Post>', $post->newQuery());
    assertType('Kasi\Types\Builder\CommonBuilder<Kasi\Types\Builder\Post>', $post->newEloquentBuilder($queryBuilder));
    assertType('Kasi\Types\Builder\CommonBuilder<Kasi\Types\Builder\Post>', $post->newModelQuery());
    assertType('Kasi\Types\Builder\CommonBuilder<Kasi\Types\Builder\Post>', $post->newQueryWithoutRelationships());
    assertType('Kasi\Types\Builder\CommonBuilder<Kasi\Types\Builder\Post>', $post->newQueryWithoutScopes());
    assertType('Kasi\Types\Builder\CommonBuilder<Kasi\Types\Builder\Post>', $post->newQueryWithoutScope('foo'));
    assertType('Kasi\Types\Builder\CommonBuilder<Kasi\Types\Builder\Post>', $post->newQueryForRestoration(1));
    assertType('Kasi\Types\Builder\CommonBuilder<Kasi\Types\Builder\Post>', $post->newQuery()->where('foo', 'bar'));
    assertType('Kasi\Types\Builder\CommonBuilder<Kasi\Types\Builder\Post>', $post->newQuery()->foo());
    assertType('Kasi\Types\Builder\Post', $post->newQuery()->create(['name' => 'John']));

    assertType('Kasi\Types\Builder\CommonBuilder<Kasi\Types\Builder\ChildPost>', ChildPost::query());
    assertType('Kasi\Types\Builder\CommonBuilder<Kasi\Types\Builder\ChildPost>', ChildPost::on());
    assertType('Kasi\Types\Builder\CommonBuilder<Kasi\Types\Builder\ChildPost>', ChildPost::onWriteConnection());
    assertType('Kasi\Types\Builder\CommonBuilder<Kasi\Types\Builder\ChildPost>', ChildPost::with([]));
    assertType('Kasi\Types\Builder\CommonBuilder<Kasi\Types\Builder\ChildPost>', $childPost->newQuery());
    assertType('Kasi\Types\Builder\CommonBuilder<Kasi\Types\Builder\ChildPost>', $childPost->newEloquentBuilder($queryBuilder));
    assertType('Kasi\Types\Builder\CommonBuilder<Kasi\Types\Builder\ChildPost>', $childPost->newModelQuery());
    assertType('Kasi\Types\Builder\CommonBuilder<Kasi\Types\Builder\ChildPost>', $childPost->newQueryWithoutRelationships());
    assertType('Kasi\Types\Builder\CommonBuilder<Kasi\Types\Builder\ChildPost>', $childPost->newQueryWithoutScopes());
    assertType('Kasi\Types\Builder\CommonBuilder<Kasi\Types\Builder\ChildPost>', $childPost->newQueryWithoutScope('foo'));
    assertType('Kasi\Types\Builder\CommonBuilder<Kasi\Types\Builder\ChildPost>', $childPost->newQueryForRestoration(1));
    assertType('Kasi\Types\Builder\CommonBuilder<Kasi\Types\Builder\ChildPost>', $childPost->newQuery()->where('foo', 'bar'));
    assertType('Kasi\Types\Builder\CommonBuilder<Kasi\Types\Builder\ChildPost>', $childPost->newQuery()->foo());
    assertType('Kasi\Types\Builder\ChildPost', $childPost->newQuery()->create(['name' => 'John']));

    assertType('Kasi\Types\Builder\CommentBuilder', Comment::query());
    assertType('Kasi\Types\Builder\CommentBuilder', Comment::on());
    assertType('Kasi\Types\Builder\CommentBuilder', Comment::onWriteConnection());
    assertType('Kasi\Types\Builder\CommentBuilder', Comment::with([]));
    assertType('Kasi\Types\Builder\CommentBuilder', $comment->newQuery());
    assertType('Kasi\Types\Builder\CommentBuilder', $comment->newEloquentBuilder($queryBuilder));
    assertType('Kasi\Types\Builder\CommentBuilder', $comment->newModelQuery());
    assertType('Kasi\Types\Builder\CommentBuilder', $comment->newQueryWithoutRelationships());
    assertType('Kasi\Types\Builder\CommentBuilder', $comment->newQueryWithoutScopes());
    assertType('Kasi\Types\Builder\CommentBuilder', $comment->newQueryWithoutScope('foo'));
    assertType('Kasi\Types\Builder\CommentBuilder', $comment->newQueryForRestoration(1));
    assertType('Kasi\Types\Builder\CommentBuilder', $comment->newQuery()->where('foo', 'bar'));
    assertType('Kasi\Types\Builder\CommentBuilder', $comment->newQuery()->foo());
    assertType('Kasi\Types\Builder\Comment', $comment->newQuery()->create(['name' => 'John']));
}

class User extends Model
{
    /** @return HasMany<Post, $this> */
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }
}

class Post extends Model
{
    /** @use HasBuilder<CommonBuilder<static>> */
    use HasBuilder;

    protected static string $builder = CommonBuilder::class;

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return MorphTo<\Kasi\Database\Eloquent\Model, $this> */
    public function taggable(): MorphTo
    {
        return $this->morphTo();
    }
}

class ChildPost extends Post
{
}

class Comment extends Model
{
    /** @use HasBuilder<CommentBuilder> */
    use HasBuilder;

    protected static string $builder = CommentBuilder::class;
}

/**
 * @template TModel of \Kasi\Database\Eloquent\Model
 *
 * @extends \Kasi\Database\Eloquent\Builder<TModel>
 */
class CommonBuilder extends Builder
{
    /** @return $this */
    public function foo(): static
    {
        return $this->where('foo', 'bar');
    }
}

/** @extends CommonBuilder<Comment> */
class CommentBuilder extends CommonBuilder
{
}
