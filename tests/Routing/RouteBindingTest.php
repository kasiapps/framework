<?php

namespace Kasi\Tests\Routing;

use Kasi\Container\Container;
use Kasi\Database\Eloquent\Model;
use Kasi\Database\Eloquent\ModelNotFoundException;
use Kasi\Database\Eloquent\SoftDeletes;
use Kasi\Routing\Route;
use Kasi\Routing\RouteBinding;
use PHPUnit\Framework\TestCase;

class RouteBindingTest extends TestCase
{
    public function test_it_can_resolve_the_explicit_model_for_the_given_route()
    {
        $container = Container::getInstance();

        $route = new Route('GET', '/users/{user}', function () {
        });

        $callback = RouteBinding::forModel($container, ExplicitRouteBindingUser::class);
        $this->assertInstanceOf(ExplicitRouteBindingUser::class, $callback(1, $route));
    }

    public function test_it_cannot_resolve_the_explicit_soft_deleted_model_for_the_given_route()
    {
        $container = Container::getInstance();

        $route = new Route('GET', '/users/{user}', function () {
        });

        $callback = RouteBinding::forModel($container, ExplicitRouteBindingSoftDeletableUser::class);

        $this->expectException(ModelNotFoundException::class);
        $callback(1, $route);
    }

    public function test_it_can_resolve_the_explicit_soft_deleted_model_for_the_given_route_with_trashed()
    {
        $container = Container::getInstance();

        $route = (new Route('GET', '/users/{user}', function () {
        }))->withTrashed();

        $callback = RouteBinding::forModel($container, ExplicitRouteBindingSoftDeletableUser::class);
        $this->assertInstanceOf(ExplicitRouteBindingSoftDeletableUser::class, $callback(1, $route));
    }
}

class ExplicitRouteBindingUser extends Model
{
    public function resolveRouteBinding($value, $field = null)
    {
        return new self();
    }
}

class ExplicitRouteBindingSoftDeletableUser extends Model
{
    use SoftDeletes;

    public function resolveRouteBinding($value, $field = null)
    {
        return null;
    }

    public function resolveSoftDeletableRouteBinding($value, $field = null)
    {
        return new self();
    }
}
