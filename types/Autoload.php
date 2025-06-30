<?php

use Kasi\Database\Eloquent\Factories\Factory;
use Kasi\Database\Eloquent\Factories\HasFactory;
use Kasi\Database\Eloquent\MassPrunable;
use Kasi\Database\Eloquent\Model;
use Kasi\Database\Eloquent\SoftDeletes;
use Kasi\Foundation\Auth\User as Authenticatable;
use Kasi\Notifications\HasDatabaseNotifications;

class User extends Authenticatable
{
    use HasDatabaseNotifications;
    /** @use HasFactory<UserFactory> */
    use HasFactory;
    use MassPrunable;
    use SoftDeletes;

    protected static string $factory = UserFactory::class;
}

/** @extends Factory<User> */
class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [];
    }
}

class Post extends Model
{
}

enum UserType
{
}
