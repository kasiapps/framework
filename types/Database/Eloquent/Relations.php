<?php

namespace Kasi\Types\Relations;

use Kasi\Database\Eloquent\Model;
use Kasi\Database\Eloquent\Relations\BelongsTo;
use Kasi\Database\Eloquent\Relations\BelongsToMany;
use Kasi\Database\Eloquent\Relations\HasMany;
use Kasi\Database\Eloquent\Relations\HasManyThrough;
use Kasi\Database\Eloquent\Relations\HasOne;
use Kasi\Database\Eloquent\Relations\HasOneThrough;
use Kasi\Database\Eloquent\Relations\MorphMany;
use Kasi\Database\Eloquent\Relations\MorphOne;
use Kasi\Database\Eloquent\Relations\MorphTo;
use Kasi\Database\Eloquent\Relations\MorphToMany;
use Kasi\Database\Eloquent\Relations\Relation;

use function PHPStan\Testing\assertType;

function test(User $user, Post $post, Comment $comment, ChildUser $child): void
{
    assertType('Kasi\Database\Eloquent\Relations\HasOne<Kasi\Types\Relations\Address, Kasi\Types\Relations\User>', $user->address());
    assertType('Kasi\Types\Relations\Address|null', $user->address()->getResults());
    assertType('Kasi\Database\Eloquent\Collection<int, Kasi\Types\Relations\Address>', $user->address()->get());
    assertType('Kasi\Types\Relations\Address', $user->address()->make());
    assertType('Kasi\Types\Relations\Address', $user->address()->create());
    assertType('Kasi\Database\Eloquent\Relations\HasOne<Kasi\Types\Relations\Address, Kasi\Types\Relations\ChildUser>', $child->address());
    assertType('Kasi\Types\Relations\Address', $child->address()->make());
    assertType('Kasi\Types\Relations\Address', $child->address()->create([]));
    assertType('Kasi\Types\Relations\Address', $child->address()->getRelated());
    assertType('Kasi\Types\Relations\ChildUser', $child->address()->getParent());

    assertType('Kasi\Database\Eloquent\Relations\HasMany<Kasi\Types\Relations\Post, Kasi\Types\Relations\User>', $user->posts());
    assertType('Kasi\Database\Eloquent\Collection<int, Kasi\Types\Relations\Post>', $user->posts()->getResults());
    assertType('Kasi\Database\Eloquent\Collection<int, Kasi\Types\Relations\Post>', $user->posts()->makeMany([]));
    assertType('Kasi\Database\Eloquent\Collection<int, Kasi\Types\Relations\Post>', $user->posts()->createMany([]));
    assertType('Kasi\Database\Eloquent\Collection<int, Kasi\Types\Relations\Post>', $user->posts()->createManyQuietly([]));
    assertType('Kasi\Database\Eloquent\Relations\HasOne<Kasi\Types\Relations\Post, Kasi\Types\Relations\User>', $user->latestPost());
    assertType('Kasi\Types\Relations\Post', $user->posts()->make());
    assertType('Kasi\Types\Relations\Post', $user->posts()->create());
    assertType('Kasi\Types\Relations\Post|false', $user->posts()->save(new Post()));
    assertType('Kasi\Types\Relations\Post|false', $user->posts()->saveQuietly(new Post()));

    assertType('Kasi\Database\Eloquent\Relations\BelongsToMany<Kasi\Types\Relations\Role, Kasi\Types\Relations\User>', $user->roles());
    assertType('Kasi\Database\Eloquent\Collection<int, Kasi\Types\Relations\Role>', $user->roles()->getResults());
    assertType('Kasi\Database\Eloquent\Collection<int, Kasi\Types\Relations\Role>', $user->roles()->find([1]));
    assertType('Kasi\Database\Eloquent\Collection<int, Kasi\Types\Relations\Role>', $user->roles()->findMany([1, 2, 3]));
    assertType('Kasi\Database\Eloquent\Collection<int, Kasi\Types\Relations\Role>', $user->roles()->findOrNew([1]));
    assertType('Kasi\Database\Eloquent\Collection<int, Kasi\Types\Relations\Role>', $user->roles()->findOrFail([1]));
    assertType('42|Kasi\Database\Eloquent\Collection<int, Kasi\Types\Relations\Role>', $user->roles()->findOr([1], fn () => 42));
    assertType('42|Kasi\Database\Eloquent\Collection<int, Kasi\Types\Relations\Role>', $user->roles()->findOr([1], callback: fn () => 42));
    assertType('Kasi\Types\Relations\Role', $user->roles()->findOrNew(1));
    assertType('Kasi\Types\Relations\Role', $user->roles()->findOrFail(1));
    assertType('Kasi\Types\Relations\Role|null', $user->roles()->find(1));
    assertType('42|Kasi\Types\Relations\Role', $user->roles()->findOr(1, fn () => 42));
    assertType('42|Kasi\Types\Relations\Role', $user->roles()->findOr(1, callback: fn () => 42));
    assertType('Kasi\Types\Relations\Role|null', $user->roles()->first());
    assertType('42|Kasi\Types\Relations\Role', $user->roles()->firstOr(fn () => 42));
    assertType('42|Kasi\Types\Relations\Role', $user->roles()->firstOr(callback: fn () => 42));
    assertType('Kasi\Types\Relations\Role|null', $user->roles()->firstWhere('foo'));
    assertType('Kasi\Types\Relations\Role', $user->roles()->firstOrNew());
    assertType('Kasi\Types\Relations\Role', $user->roles()->firstOrFail());
    assertType('Kasi\Types\Relations\Role', $user->roles()->firstOrCreate());
    assertType('Kasi\Types\Relations\Role', $user->roles()->create());
    assertType('Kasi\Types\Relations\Role', $user->roles()->createOrFirst());
    assertType('Kasi\Types\Relations\Role', $user->roles()->updateOrCreate([]));
    assertType('Kasi\Types\Relations\Role', $user->roles()->save(new Role()));
    assertType('Kasi\Types\Relations\Role', $user->roles()->saveQuietly(new Role()));
    $roles = $user->roles()->getResults();
    assertType('Kasi\Database\Eloquent\Collection<int, Kasi\Types\Relations\Role>', $user->roles()->saveMany($roles));
    assertType('array<int, Kasi\Types\Relations\Role>', $user->roles()->saveMany($roles->all()));
    assertType('Kasi\Database\Eloquent\Collection<int, Kasi\Types\Relations\Role>', $user->roles()->saveManyQuietly($roles));
    assertType('array<int, Kasi\Types\Relations\Role>', $user->roles()->saveManyQuietly($roles->all()));
    assertType('array<int, Kasi\Types\Relations\Role>', $user->roles()->createMany($roles));
    assertType('array{attached: array, detached: array, updated: array}', $user->roles()->sync($roles));
    assertType('array{attached: array, detached: array, updated: array}', $user->roles()->syncWithoutDetaching($roles));
    assertType('array{attached: array, detached: array, updated: array}', $user->roles()->syncWithPivotValues($roles, []));
    assertType('Kasi\Support\LazyCollection<int, Kasi\Types\Relations\Role>', $user->roles()->lazy());
    assertType('Kasi\Support\LazyCollection<int, Kasi\Types\Relations\Role>', $user->roles()->lazyById());
    assertType('Kasi\Support\LazyCollection<int, Kasi\Types\Relations\Role>', $user->roles()->cursor());

    assertType('Kasi\Database\Eloquent\Relations\HasOneThrough<Kasi\Types\Relations\Car, Kasi\Types\Relations\Mechanic, Kasi\Types\Relations\User>', $user->car());
    assertType('Kasi\Types\Relations\Car|null', $user->car()->getResults());
    assertType('Kasi\Database\Eloquent\Collection<int, Kasi\Types\Relations\Car>', $user->car()->find([1]));
    assertType('42|Kasi\Database\Eloquent\Collection<int, Kasi\Types\Relations\Car>', $user->car()->findOr([1], fn () => 42));
    assertType('42|Kasi\Database\Eloquent\Collection<int, Kasi\Types\Relations\Car>', $user->car()->findOr([1], callback: fn () => 42));
    assertType('Kasi\Types\Relations\Car|null', $user->car()->find(1));
    assertType('42|Kasi\Types\Relations\Car', $user->car()->findOr(1, fn () => 42));
    assertType('42|Kasi\Types\Relations\Car', $user->car()->findOr(1, callback: fn () => 42));
    assertType('Kasi\Types\Relations\Car|null', $user->car()->first());
    assertType('42|Kasi\Types\Relations\Car', $user->car()->firstOr(fn () => 42));
    assertType('42|Kasi\Types\Relations\Car', $user->car()->firstOr(callback: fn () => 42));
    assertType('Kasi\Support\LazyCollection<int, Kasi\Types\Relations\Car>', $user->car()->lazy());
    assertType('Kasi\Support\LazyCollection<int, Kasi\Types\Relations\Car>', $user->car()->lazyById());
    assertType('Kasi\Support\LazyCollection<int, Kasi\Types\Relations\Car>', $user->car()->cursor());

    assertType('Kasi\Database\Eloquent\Relations\HasManyThrough<Kasi\Types\Relations\Part, Kasi\Types\Relations\Mechanic, Kasi\Types\Relations\User>', $user->parts());
    assertType('Kasi\Database\Eloquent\Collection<int, Kasi\Types\Relations\Part>', $user->parts()->getResults());
    assertType('Kasi\Database\Eloquent\Relations\HasOneThrough<Kasi\Types\Relations\Part, Kasi\Types\Relations\Mechanic, Kasi\Types\Relations\User>', $user->firstPart());

    assertType('Kasi\Database\Eloquent\Relations\BelongsTo<Kasi\Types\Relations\User, Kasi\Types\Relations\Post>', $post->user());
    assertType('Kasi\Types\Relations\User|null', $post->user()->getResults());
    assertType('Kasi\Types\Relations\User', $post->user()->make());
    assertType('Kasi\Types\Relations\User', $post->user()->create());
    assertType('Kasi\Types\Relations\Post', $post->user()->associate(new User()));
    assertType('Kasi\Types\Relations\Post', $post->user()->dissociate());
    assertType('Kasi\Types\Relations\Post', $post->user()->disassociate());
    assertType('Kasi\Types\Relations\Post', $post->user()->getChild());

    assertType('Kasi\Database\Eloquent\Relations\MorphOne<Kasi\Types\Relations\Image, Kasi\Types\Relations\Post>', $post->image());
    assertType('Kasi\Types\Relations\Image|null', $post->image()->getResults());
    assertType('Kasi\Types\Relations\Image', $post->image()->forceCreate([]));

    assertType('Kasi\Database\Eloquent\Relations\MorphMany<Kasi\Types\Relations\Comment, Kasi\Types\Relations\Post>', $post->comments());
    assertType('Kasi\Database\Eloquent\Collection<int, Kasi\Types\Relations\Comment>', $post->comments()->getResults());
    assertType('Kasi\Database\Eloquent\Relations\MorphOne<Kasi\Types\Relations\Comment, Kasi\Types\Relations\Post>', $post->latestComment());

    assertType('Kasi\Database\Eloquent\Relations\MorphTo<Kasi\Database\Eloquent\Model, Kasi\Types\Relations\Comment>', $comment->commentable());
    assertType('Kasi\Database\Eloquent\Model|null', $comment->commentable()->getResults());
    assertType('Kasi\Database\Eloquent\Collection<int, Kasi\Types\Relations\Comment>', $comment->commentable()->getEager());
    assertType('Kasi\Database\Eloquent\Model', $comment->commentable()->createModelByType('foo'));
    assertType('Kasi\Types\Relations\Comment', $comment->commentable()->associate(new Post()));
    assertType('Kasi\Types\Relations\Comment', $comment->commentable()->dissociate());

    assertType('Kasi\Database\Eloquent\Relations\MorphToMany<Kasi\Types\Relations\Tag, Kasi\Types\Relations\Post>', $post->tags());
    assertType('Kasi\Database\Eloquent\Collection<int, Kasi\Types\Relations\Tag>', $post->tags()->getResults());

    assertType('42', Relation::noConstraints(fn () => 42));
}

class User extends Model
{
    /** @return HasOne<Address, $this> */
    public function address(): HasOne
    {
        $hasOne = $this->hasOne(Address::class);
        assertType('Kasi\Database\Eloquent\Relations\HasOne<Kasi\Types\Relations\Address, $this(Kasi\Types\Relations\User)>', $hasOne);

        return $hasOne;
    }

    /** @return HasMany<Post, $this> */
    public function posts(): HasMany
    {
        $hasMany = $this->hasMany(Post::class);
        assertType('Kasi\Database\Eloquent\Relations\HasMany<Kasi\Types\Relations\Post, $this(Kasi\Types\Relations\User)>', $hasMany);

        return $hasMany;
    }

    /** @return HasOne<Post, $this> */
    public function latestPost(): HasOne
    {
        $post = $this->posts()->one();
        assertType('Kasi\Database\Eloquent\Relations\HasOne<Kasi\Types\Relations\Post, $this(Kasi\Types\Relations\User)>', $post);

        return $post;
    }

    /** @return BelongsToMany<Role, $this> */
    public function roles(): BelongsToMany
    {
        $belongsToMany = $this->belongsToMany(Role::class);
        assertType('Kasi\Database\Eloquent\Relations\BelongsToMany<Kasi\Types\Relations\Role, $this(Kasi\Types\Relations\User)>', $belongsToMany);

        return $belongsToMany;
    }

    /** @return HasOne<Mechanic, $this> */
    public function mechanic(): HasOne
    {
        return $this->hasOne(Mechanic::class);
    }

    /** @return HasMany<Mechanic, $this> */
    public function mechanics(): HasMany
    {
        return $this->hasMany(Mechanic::class);
    }

    /** @return HasOneThrough<Car, Mechanic, $this> */
    public function car(): HasOneThrough
    {
        $hasOneThrough = $this->hasOneThrough(Car::class, Mechanic::class);
        assertType('Kasi\Database\Eloquent\Relations\HasOneThrough<Kasi\Types\Relations\Car, Kasi\Types\Relations\Mechanic, $this(Kasi\Types\Relations\User)>', $hasOneThrough);

        $through = $this->through('mechanic');
        assertType(
            'Kasi\Database\Eloquent\PendingHasThroughRelationship<Kasi\Database\Eloquent\Model, $this(Kasi\Types\Relations\User)>',
            $through,
        );
        assertType(
            'Kasi\Database\Eloquent\Relations\HasManyThrough<Kasi\Database\Eloquent\Model, Kasi\Database\Eloquent\Model, $this(Kasi\Types\Relations\User)>|Kasi\Database\Eloquent\Relations\HasOneThrough<Kasi\Database\Eloquent\Model, Kasi\Database\Eloquent\Model, $this(Kasi\Types\Relations\User)>',
            $through->has('car'),
        );

        $through = $this->through($this->mechanic());
        assertType(
            'Kasi\Database\Eloquent\PendingHasThroughRelationship<Kasi\Types\Relations\Mechanic, $this(Kasi\Types\Relations\User), Kasi\Database\Eloquent\Relations\HasOne<Kasi\Types\Relations\Mechanic, $this(Kasi\Types\Relations\User)>>',
            $through,
        );
        assertType(
            'Kasi\Database\Eloquent\Relations\HasOneThrough<Kasi\Types\Relations\Car, Kasi\Types\Relations\Mechanic, $this(Kasi\Types\Relations\User)>',
            $through->has(function ($mechanic) {
                assertType('Kasi\Types\Relations\Mechanic', $mechanic);

                return $mechanic->car();
            }),
        );

        return $hasOneThrough;
    }

    /** @return HasManyThrough<Car, Mechanic, $this> */
    public function cars(): HasManyThrough
    {
        $through = $this->through($this->mechanics());
        assertType(
            'Kasi\Database\Eloquent\PendingHasThroughRelationship<Kasi\Types\Relations\Mechanic, $this(Kasi\Types\Relations\User), Kasi\Database\Eloquent\Relations\HasMany<Kasi\Types\Relations\Mechanic, $this(Kasi\Types\Relations\User)>>',
            $through,
        );
        $hasManyThrough = $through->has(function ($mechanic) {
            assertType('Kasi\Types\Relations\Mechanic', $mechanic);

            return $mechanic->car();
        });
        assertType(
            'Kasi\Database\Eloquent\Relations\HasManyThrough<Kasi\Types\Relations\Car, Kasi\Types\Relations\Mechanic, $this(Kasi\Types\Relations\User)>',
            $hasManyThrough,
        );

        return $hasManyThrough;
    }

    /** @return HasManyThrough<Part, Mechanic, $this> */
    public function parts(): HasManyThrough
    {
        $hasManyThrough = $this->hasManyThrough(Part::class, Mechanic::class);
        assertType('Kasi\Database\Eloquent\Relations\HasManyThrough<Kasi\Types\Relations\Part, Kasi\Types\Relations\Mechanic, $this(Kasi\Types\Relations\User)>', $hasManyThrough);

        assertType(
            'Kasi\Database\Eloquent\Relations\HasManyThrough<Kasi\Types\Relations\Part, Kasi\Types\Relations\Mechanic, $this(Kasi\Types\Relations\User)>',
            $this->through($this->mechanic())->has(fn ($mechanic) => $mechanic->parts()),
        );

        return $hasManyThrough;
    }

    /** @return HasOneThrough<Part, Mechanic, $this> */
    public function firstPart(): HasOneThrough
    {
        $part = $this->parts()->one();
        assertType('Kasi\Database\Eloquent\Relations\HasOneThrough<Kasi\Types\Relations\Part, Kasi\Types\Relations\Mechanic, $this(Kasi\Types\Relations\User)>', $part);

        return $part;
    }
}

class Post extends Model
{
    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        $belongsTo = $this->belongsTo(User::class);
        assertType('Kasi\Database\Eloquent\Relations\BelongsTo<Kasi\Types\Relations\User, $this(Kasi\Types\Relations\Post)>', $belongsTo);

        return $belongsTo;
    }

    /** @return MorphOne<Image, $this> */
    public function image(): MorphOne
    {
        $morphOne = $this->morphOne(Image::class, 'imageable');
        assertType('Kasi\Database\Eloquent\Relations\MorphOne<Kasi\Types\Relations\Image, $this(Kasi\Types\Relations\Post)>', $morphOne);

        return $morphOne;
    }

    /** @return MorphMany<Comment, $this> */
    public function comments(): MorphMany
    {
        $morphMany = $this->morphMany(Comment::class, 'commentable');
        assertType('Kasi\Database\Eloquent\Relations\MorphMany<Kasi\Types\Relations\Comment, $this(Kasi\Types\Relations\Post)>', $morphMany);

        return $morphMany;
    }

    /** @return MorphOne<Comment, $this> */
    public function latestComment(): MorphOne
    {
        $comment = $this->comments()->one();
        assertType('Kasi\Database\Eloquent\Relations\MorphOne<Kasi\Types\Relations\Comment, $this(Kasi\Types\Relations\Post)>', $comment);

        return $comment;
    }

    /** @return MorphToMany<Tag, $this> */
    public function tags(): MorphToMany
    {
        $morphToMany = $this->morphedByMany(Tag::class, 'taggable');
        assertType('Kasi\Database\Eloquent\Relations\MorphToMany<Kasi\Types\Relations\Tag, $this(Kasi\Types\Relations\Post)>', $morphToMany);

        return $morphToMany;
    }
}

class Comment extends Model
{
    /** @return MorphTo<\Kasi\Database\Eloquent\Model, $this> */
    public function commentable(): MorphTo
    {
        $morphTo = $this->morphTo();
        assertType('Kasi\Database\Eloquent\Relations\MorphTo<Kasi\Database\Eloquent\Model, $this(Kasi\Types\Relations\Comment)>', $morphTo);

        return $morphTo;
    }
}

class Tag extends Model
{
    /** @return MorphToMany<Post, $this> */
    public function posts(): MorphToMany
    {
        $morphToMany = $this->morphToMany(Post::class, 'taggable');
        assertType('Kasi\Database\Eloquent\Relations\MorphToMany<Kasi\Types\Relations\Post, $this(Kasi\Types\Relations\Tag)>', $morphToMany);

        return $morphToMany;
    }
}

class Mechanic extends Model
{
    /** @return HasOne<Car, $this> */
    public function car(): HasOne
    {
        return $this->hasOne(Car::class);
    }

    /** @return HasMany<Part, $this> */
    public function parts(): HasMany
    {
        return $this->hasMany(Part::class);
    }
}

class ChildUser extends User
{
}
class Address extends Model
{
}
class Role extends Model
{
}
class Car extends Model
{
}
class Part extends Model
{
}
class Image extends Model
{
}
