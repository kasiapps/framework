<?php

namespace Kasi\Types\Model;

use Kasi\Database\Eloquent\Attributes\CollectedBy;
use Kasi\Database\Eloquent\Collection;
use Kasi\Database\Eloquent\HasCollection;
use Kasi\Database\Eloquent\Model;
use User;

use function PHPStan\Testing\assertType;

function test(User $user, Post $post, Comment $comment, Article $article): void
{
    assertType('UserFactory', User::factory(function ($attributes, $model) {
        assertType('array<string, mixed>', $attributes);
        assertType('User|null', $model);

        return ['string' => 'string'];
    }));
    assertType('UserFactory', User::factory(42, function ($attributes, $model) {
        assertType('array<string, mixed>', $attributes);
        assertType('User|null', $model);

        return ['string' => 'string'];
    }));

    User::addGlobalScope('ancient', function ($builder) {
        assertType('Kasi\Database\Eloquent\Builder<User>', $builder);

        $builder->where('created_at', '<', now()->subYears(2000));
    });

    assertType('Kasi\Database\Eloquent\Builder<User>', User::query());
    assertType('Kasi\Database\Eloquent\Builder<User>', $user->newQuery());
    assertType('Kasi\Database\Eloquent\Builder<User>', $user->withTrashed());
    assertType('Kasi\Database\Eloquent\Builder<User>', $user->onlyTrashed());
    assertType('Kasi\Database\Eloquent\Builder<User>', $user->withoutTrashed());
    assertType('Kasi\Database\Eloquent\Builder<User>', $user->prunable());
    assertType('Kasi\Database\Eloquent\Relations\MorphMany<Kasi\Notifications\DatabaseNotification, User>', $user->notifications());

    assertType('Kasi\Database\Eloquent\Collection<(int|string), User>', $user->newCollection([new User()]));
    assertType('Kasi\Types\Model\Posts<(int|string), Kasi\Types\Model\Post>', $post->newCollection(['foo' => new Post()]));
    assertType('Kasi\Types\Model\Articles<(int|string), Kasi\Types\Model\Article>', $article->newCollection([new Article()]));
    assertType('Kasi\Types\Model\Comments', $comment->newCollection([new Comment()]));

    assertType('bool', $user->restore());
    assertType('User', $user->restoreOrCreate());
    assertType('User', $user->createOrRestore());
}

class Post extends Model
{
    /** @use HasCollection<Posts<array-key, static>> */
    use HasCollection;

    protected static string $collectionClass = Posts::class;
}

/**
 * @template TKey of array-key
 * @template TModel of Post
 *
 * @extends Collection<TKey, TModel> */
class Posts extends Collection
{
}

final class Comment extends Model
{
    /** @use HasCollection<Comments> */
    use HasCollection;

    /** @param  array<array-key, Comment>  $models */
    public function newCollection(array $models = []): Comments
    {
        return new Comments($models);
    }
}

/** @extends Collection<array-key, Comment> */
final class Comments extends Collection
{
}

#[CollectedBy(Articles::class)]
class Article extends Model
{
    /** @use HasCollection<Articles<array-key, static>> */
    use HasCollection;
}

/**
 * @template TKey of array-key
 * @template TModel of Article
 *
 * @extends Collection<TKey, TModel> */
class Articles extends Collection
{
}
