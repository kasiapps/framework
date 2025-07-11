<?php

namespace Kasi\Tests\Routing;

use Kasi\Container\Container;
use Kasi\Database\Eloquent\Model;
use Kasi\Routing\Exceptions\BackedEnumCaseNotFoundException;
use Kasi\Routing\ImplicitRouteBinding;
use Kasi\Routing\Route;
use PHPUnit\Framework\TestCase;

include_once 'Enums.php';

class ImplicitRouteBindingTest extends TestCase
{
    public function test_it_can_resolve_the_implicit_backed_enum_route_bindings_for_the_given_route()
    {
        $action = ['uses' => function (CategoryBackedEnum $category) {
            return $category->value;
        }];

        $route = new Route('GET', '/test', $action);
        $route->parameters = ['category' => 'fruits'];

        $route->prepareForSerialization();

        $container = Container::getInstance();

        ImplicitRouteBinding::resolveForRoute($container, $route);

        $this->assertSame('fruits', $route->parameter('category')->value);
    }

    public function test_it_can_resolve_the_implicit_backed_enum_route_bindings_for_the_given_route_with_optional_parameter()
    {
        $action = ['uses' => function (?CategoryBackedEnum $category = null) {
            return $category->value;
        }];

        $route = new Route('GET', '/test', $action);
        $route->parameters = ['category' => 'fruits'];

        $route->prepareForSerialization();

        $container = Container::getInstance();

        ImplicitRouteBinding::resolveForRoute($container, $route);

        $this->assertSame('fruits', $route->parameter('category')->value);
    }

    public function test_it_handles_optional_implicit_backed_enum_route_bindings_for_the_given_route_with_optional_parameter()
    {
        $action = ['uses' => function (?CategoryBackedEnum $category = null) {
            return $category->value;
        }];

        $route = new Route('GET', '/test', $action);
        $route->parameters = ['category' => null];

        $route->prepareForSerialization();

        $container = Container::getInstance();

        ImplicitRouteBinding::resolveForRoute($container, $route);

        $this->assertNull($route->parameter('category'));
    }

    public function test_it_does_not_resolve_implicit_non_backed_enum_route_bindings_for_the_given_route()
    {
        $action = ['uses' => function (CategoryEnum $category) {
            return $category->value;
        }];

        $route = new Route('GET', '/test', $action);
        $route->parameters = ['category' => 'fruits'];

        $route->prepareForSerialization();

        $container = Container::getInstance();

        ImplicitRouteBinding::resolveForRoute($container, $route);

        $this->assertIsString($route->parameter('category'));
        $this->assertSame('fruits', $route->parameter('category'));
    }

    public function test_implicit_backed_enum_internal_exception()
    {
        $action = ['uses' => function (CategoryBackedEnum $category) {
            return $category->value;
        }];

        $route = new Route('GET', '/test', $action);
        $route->parameters = ['category' => 'cars'];

        $route->prepareForSerialization();

        $container = Container::getInstance();

        $this->expectException(BackedEnumCaseNotFoundException::class);
        $this->expectExceptionMessage(sprintf(
            'Case [%s] not found on Backed Enum [%s].',
            'cars',
            CategoryBackedEnum::class,
        ));

        ImplicitRouteBinding::resolveForRoute($container, $route);
    }

    public function test_it_can_resolve_the_implicit_model_route_bindings_for_the_given_route()
    {
        $this->expectNotToPerformAssertions();

        $action = ['uses' => function (ImplicitRouteBindingUser $user) {
            return $user;
        }];

        $route = new Route('GET', '/test', $action);
        $route->parameters = ['user' => new ImplicitRouteBindingUser];

        $route->prepareForSerialization();

        $container = Container::getInstance();

        ImplicitRouteBinding::resolveForRoute($container, $route);
    }
}

class ImplicitRouteBindingUser extends Model
{
    //
}
