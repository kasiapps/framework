<?php

declare(strict_types=1);

use Laravel\Lumen\Http\Request;
use Laravel\Lumen\Routing\UrlGenerator;
use Illuminate\Contracts\Routing\UrlRoutable;

beforeEach(function () {
    // Use the existing app from TestCase to avoid risky tests
    $this->app->instance('request', Request::create('http://localhost', 'GET'));
    $this->urlGenerator = new UrlGenerator($this->app);
});

it('tests full method', function () {
    // Mock request with fullUrl method
    $mockRequest = \Mockery::mock(Request::class);
    $mockRequest->shouldReceive('fullUrl')->andReturn('http://localhost/test?param=value');

    $this->app->instance('request', $mockRequest);
    $urlGenerator = new UrlGenerator($this->app);

    $result = $urlGenerator->full();

    expect($result)->toBe('http://localhost/test?param=value');
});

it('tests current method', function () {
    // Mock request with all needed methods
    $mockRequest = \Mockery::mock(Request::class);
    $mockRequest->shouldReceive('getPathInfo')->andReturn('/current');
    $mockRequest->shouldReceive('getScheme')->andReturn('http');
    $mockRequest->shouldReceive('root')->andReturn('http://localhost');

    $this->app->instance('request', $mockRequest);
    $urlGenerator = new UrlGenerator($this->app);

    $result = $urlGenerator->current();

    expect($result)->toContain('/current');
});

it('tests to method with valid URL', function () {
    $result = $this->urlGenerator->to('http://example.com/test');

    expect($result)->toBe('http://example.com/test');
});

it('tests to method with path', function () {
    $result = $this->urlGenerator->to('/test/path');

    expect($result)->toContain('/test/path');
});

it('tests to method with extra parameters', function () {
    $result = $this->urlGenerator->to('/test', ['param1', 'param2']);

    expect($result)->toContain('/test/param1/param2');
});

it('tests to method with secure parameter', function () {
    $result = $this->urlGenerator->to('/test', [], true);

    expect($result)->toStartWith('https://');
});

it('tests secure method', function () {
    $result = $this->urlGenerator->secure('/test', ['param']);

    expect($result)->toStartWith('https://');
    expect($result)->toContain('/test/param');
});

it('tests asset method with valid URL', function () {
    $result = $this->urlGenerator->asset('http://cdn.example.com/style.css');

    expect($result)->toBe('http://cdn.example.com/style.css');
});

it('tests asset method with path', function () {
    $result = $this->urlGenerator->asset('css/style.css');

    expect($result)->toContain('/css/style.css');
});

it('tests asset method with secure parameter', function () {
    $result = $this->urlGenerator->asset('css/style.css', true);

    expect($result)->toStartWith('https://');
});

it('tests assetFrom method', function () {
    $result = $this->urlGenerator->assetFrom('http://cdn.example.com', 'css/style.css');

    expect($result)->toBe('http://cdn.example.com/css/style.css');
});

it('tests assetFrom method with secure parameter', function () {
    $result = $this->urlGenerator->assetFrom('http://cdn.example.com', 'css/style.css', true);

    expect($result)->toStartWith('https://');
});

it('tests secureAsset method', function () {
    $result = $this->urlGenerator->secureAsset('css/style.css');

    expect($result)->toStartWith('https://');
    expect($result)->toContain('/css/style.css');
});

it('tests forceScheme method', function () {
    $this->urlGenerator->forceScheme('https');

    $result = $this->urlGenerator->to('/test');

    expect($result)->toStartWith('https://');
});

it('tests formatScheme method with secure true', function () {
    $result = $this->urlGenerator->formatScheme(true);

    expect($result)->toBe('https://');
});

it('tests formatScheme method with secure false', function () {
    $result = $this->urlGenerator->formatScheme(false);

    expect($result)->toBe('http://');
});

it('tests formatScheme method with null', function () {
    $result = $this->urlGenerator->formatScheme(null);

    expect($result)->toBeString();
    expect($result)->toMatch('/^https?:\/\/$/');
});

it('tests route method with valid route', function () {
    // Set up a named route
    $this->app->router->get('/users/{id}', ['as' => 'user.show', function () {}]);

    $result = $this->urlGenerator->route('user.show', ['id' => 123]);

    expect($result)->toContain('/users/123');
});

it('tests route method with invalid route', function () {
    expect(function () {
        $this->urlGenerator->route('nonexistent.route');
    })->toThrow(\InvalidArgumentException::class);
});

it('tests route method with optional parameters', function () {
    // Set up a route with optional parameters
    $this->app->router->get('/posts/{id}[/{slug}]', ['as' => 'post.show', function () {}]);

    $result = $this->urlGenerator->route('post.show', ['id' => 123]);

    expect($result)->toContain('/posts/123');
});

it('tests route method with query parameters', function () {
    $this->app->router->get('/search', ['as' => 'search', function () {}]);

    $result = $this->urlGenerator->route('search', ['q' => 'test', 'page' => 2]);

    expect($result)->toContain('/search?q=test&page=2');
});

it('tests isValidUrl method with various URLs', function () {
    expect($this->urlGenerator->isValidUrl('http://example.com'))->toBeTrue();
    expect($this->urlGenerator->isValidUrl('https://example.com'))->toBeTrue();
    expect($this->urlGenerator->isValidUrl('//example.com'))->toBeTrue();
    expect($this->urlGenerator->isValidUrl('mailto:test@example.com'))->toBeTrue();
    expect($this->urlGenerator->isValidUrl('tel:+1234567890'))->toBeTrue();
    expect($this->urlGenerator->isValidUrl('#anchor'))->toBeTrue();
    expect($this->urlGenerator->isValidUrl('/relative/path'))->toBeFalse();
    expect($this->urlGenerator->isValidUrl('relative/path'))->toBeFalse();
});

it('tests formatParameters method with array', function () {
    $result = $this->urlGenerator->formatParameters(['param1', 'param2']);

    expect($result)->toBe(['param1', 'param2']);
});

it('tests formatParameters method with UrlRoutable', function () {
    $routable = \Mockery::mock(UrlRoutable::class);
    $routable->shouldReceive('getRouteKey')->andReturn('123');

    $result = $this->urlGenerator->formatParameters([$routable]);

    expect($result)->toBe(['123']);
});

it('tests formatParameters method with single parameter', function () {
    $result = $this->urlGenerator->formatParameters('single');

    expect($result)->toBe(['single']);
});

it('tests removeIndex method with index.php in URL', function () {
    // Use reflection to test protected method
    $reflection = new ReflectionClass($this->urlGenerator);
    $method = $reflection->getMethod('removeIndex');
    $method->setAccessible(true);

    $result = $method->invoke($this->urlGenerator, 'http://localhost/index.php/test');

    expect($result)->toBe('http://localhost/test');
});

it('tests removeIndex method without index.php', function () {
    // Use reflection to test protected method
    $reflection = new ReflectionClass($this->urlGenerator);
    $method = $reflection->getMethod('removeIndex');
    $method->setAccessible(true);

    $result = $method->invoke($this->urlGenerator, 'http://localhost/test');

    expect($result)->toBe('http://localhost/test');
});

it('tests getSchemeForUrl method with secure parameter', function () {
    // Use reflection to test protected method
    $reflection = new ReflectionClass($this->urlGenerator);
    $method = $reflection->getMethod('getSchemeForUrl');
    $method->setAccessible(true);

    $result = $method->invoke($this->urlGenerator, true);
    expect($result)->toBe('https://');

    $result = $method->invoke($this->urlGenerator, false);
    expect($result)->toBe('http://');
});

it('tests getSchemeForUrl method with null parameter', function () {
    // Use reflection to test protected method
    $reflection = new ReflectionClass($this->urlGenerator);
    $method = $reflection->getMethod('getSchemeForUrl');
    $method->setAccessible(true);

    $result = $method->invoke($this->urlGenerator, null);

    expect($result)->toBeString();
    expect($result)->toMatch('/^https?:\/\/$/');
});

it('tests replaceRouteParameters method', function () {
    // Use reflection to test protected method
    $reflection = new ReflectionClass($this->urlGenerator);
    $method = $reflection->getMethod('replaceRouteParameters');
    $method->setAccessible(true);

    $parameters = ['id' => '123', 'slug' => 'test-post'];
    $result = $method->invokeArgs($this->urlGenerator, ['/posts/{id}/{slug}', &$parameters]);

    expect($result)->toBe('/posts/123/test-post');
});

it('tests replaceRouteParameters method with unused parameters', function () {
    // Use reflection to test protected method
    $reflection = new ReflectionClass($this->urlGenerator);
    $method = $reflection->getMethod('replaceRouteParameters');
    $method->setAccessible(true);

    $parameters = ['id' => '123', 'unused' => 'value'];
    $result = $method->invokeArgs($this->urlGenerator, ['/posts/{id}', &$parameters]);

    expect($result)->toBe('/posts/123');
    expect($parameters)->toBe(['unused' => 'value']); // unused parameter should remain
});
