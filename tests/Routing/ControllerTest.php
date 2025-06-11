<?php

declare(strict_types=1);

use Laravel\Lumen\Routing\Controller;

it('defines middleware on controller', function () {
    $controller = new class extends Controller {
        public function getMiddleware()
        {
            return $this->middleware;
        }
    };
    
    $controller->middleware('auth');
    $controller->middleware('throttle', ['only' => ['store', 'update']]);
    $controller->middleware('verified', ['except' => ['index']]);
    
    $middleware = $controller->getMiddleware();
    
    expect($middleware)->toHaveKey('auth');
    expect($middleware['auth'])->toBe([]);
    expect($middleware)->toHaveKey('throttle');
    expect($middleware['throttle'])->toBe(['only' => ['store', 'update']]);
    expect($middleware)->toHaveKey('verified');
    expect($middleware['verified'])->toBe(['except' => ['index']]);
});

it('gets middleware for method with only option', function () {
    $controller = new class extends Controller {};
    
    $controller->middleware('auth');
    $controller->middleware('throttle', ['only' => ['store', 'update']]);
    $controller->middleware('verified', ['only' => ['store']]);
    
    $middleware = $controller->getMiddlewareForMethod('store');
    
    expect($middleware)->toContain('auth');
    expect($middleware)->toContain('throttle');
    expect($middleware)->toContain('verified');
    
    $middleware = $controller->getMiddlewareForMethod('index');
    
    expect($middleware)->toContain('auth');
    expect($middleware)->not->toContain('throttle');
    expect($middleware)->not->toContain('verified');
});

it('gets middleware for method with except option', function () {
    $controller = new class extends Controller {};
    
    $controller->middleware('auth');
    $controller->middleware('throttle', ['except' => ['index', 'show']]);
    $controller->middleware('verified', ['except' => ['index']]);
    
    $middleware = $controller->getMiddlewareForMethod('index');
    
    expect($middleware)->toContain('auth');
    expect($middleware)->not->toContain('throttle');
    expect($middleware)->not->toContain('verified');
    
    $middleware = $controller->getMiddlewareForMethod('store');
    
    expect($middleware)->toContain('auth');
    expect($middleware)->toContain('throttle');
    expect($middleware)->toContain('verified');
});

it('gets middleware for method with mixed options', function () {
    $controller = new class extends Controller {};
    
    $controller->middleware('auth');
    $controller->middleware('throttle', ['only' => ['store', 'update']]);
    $controller->middleware('verified', ['except' => ['index', 'show']]);
    $controller->middleware('admin', ['only' => ['destroy']]);
    
    $middleware = $controller->getMiddlewareForMethod('store');
    
    expect($middleware)->toContain('auth');
    expect($middleware)->toContain('throttle');
    expect($middleware)->toContain('verified');
    expect($middleware)->not->toContain('admin');
    
    $middleware = $controller->getMiddlewareForMethod('destroy');
    
    expect($middleware)->toContain('auth');
    expect($middleware)->not->toContain('throttle');
    expect($middleware)->toContain('verified');
    expect($middleware)->toContain('admin');
});

it('returns empty array when no middleware defined', function () {
    $controller = new class extends Controller {};
    
    $middleware = $controller->getMiddlewareForMethod('index');
    
    expect($middleware)->toBe([]);
});
