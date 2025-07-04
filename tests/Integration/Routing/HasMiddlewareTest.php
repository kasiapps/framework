<?php

namespace Kasi\Tests\Integration\Routing;

use Kasi\Routing\Controllers\HasMiddleware;
use Kasi\Routing\Controllers\Middleware;
use Kasi\Support\Facades\Route;
use Orchestra\Testbench\TestCase;

class HasMiddlewareTest extends TestCase
{
    public function test_has_middleware_is_respected()
    {
        $route = Route::get('/', [HasMiddlewareTestController::class, 'index']);
        $this->assertEquals($route->controllerMiddleware(), ['all', 'only-index']);

        $route = Route::get('/', [HasMiddlewareTestController::class, 'show']);
        $this->assertEquals($route->controllerMiddleware(), ['all', 'except-index']);
    }
}

class HasMiddlewareTestController implements HasMiddleware
{
    public static function middleware()
    {
        return [
            new Middleware('all'),
            (new Middleware('only-index'))->only('index'),
            (new Middleware('except-index'))->except('index'),
        ];
    }

    public function index()
    {
        //
    }

    public function show()
    {
    }
}
