<?php

declare(strict_types=1);

use Laravel\Lumen\Testing\Concerns\MakesHttpRequests;
use Mockery as m;

afterEach(function () {
    m::close();
});

// Helper function to set the protected response property
function setResponseProperty($instance, $response)
{
    $responseProperty = new ReflectionProperty($instance, 'response');
    $responseProperty->setAccessible(true);
    $responseProperty->setValue($instance, $response);
}

// Helper function to create a test instance with the MakesHttpRequests trait
function createHttpRequestsTestInstance()
{
    return new class {
        use MakesHttpRequests;

        public $app;
        public $baseUrl = 'http://localhost';

        public function setApp($app)
        {
            $this->app = $app;
        }

        // Make protected methods public for testing
        public function callReceiveJson($data = null)
        {
            $method = new ReflectionMethod($this, 'receiveJson');
            $method->setAccessible(true);
            return $method->invoke($this, $data);
        }

        public function callHandle($request)
        {
            $method = new ReflectionMethod($this, 'handle');
            $method->setAccessible(true);
            return $method->invoke($this, $request);
        }
    };
}

it('has makes http requests trait methods', function () {
    // Test that we can create an instance with the trait
    $testInstance = createHttpRequestsTestInstance();
    expect($testInstance)->toBeObject();

    // Test that the trait methods exist on the instance
    expect(method_exists($testInstance, 'json'))->toBeTrue();
    expect(method_exists($testInstance, 'get'))->toBeTrue();
    expect(method_exists($testInstance, 'post'))->toBeTrue();
    expect(method_exists($testInstance, 'handle'))->toBeTrue();
    expect(method_exists($testInstance, 'call'))->toBeTrue();

    // Test that our helper methods exist
    expect(method_exists($testInstance, 'callReceiveJson'))->toBeTrue();
    expect(method_exists($testInstance, 'callHandle'))->toBeTrue();
});

it('transforms headers to server vars', function () {
    $testInstance = createHttpRequestsTestInstance();

    // Use reflection to access the protected method
    $method = new ReflectionMethod($testInstance, 'transformHeadersToServerVars');
    $method->setAccessible(true);

    $headers = [
        'Content-Type' => 'application/json',
        'Accept' => 'application/json',
        'X-Custom-Header' => 'custom-value'
    ];

    $result = $method->invoke($testInstance, $headers);

    expect($result)->toHaveKey('CONTENT_TYPE');
    expect($result['CONTENT_TYPE'])->toBe('application/json');
    expect($result)->toHaveKey('HTTP_ACCEPT');
    expect($result['HTTP_ACCEPT'])->toBe('application/json');
    expect($result)->toHaveKey('HTTP_X_CUSTOM_HEADER');
    expect($result['HTTP_X_CUSTOM_HEADER'])->toBe('custom-value');
});

it('prepares url for request', function () {
    $testInstance = createHttpRequestsTestInstance();
    $testInstance->baseUrl = 'http://localhost';

    // Use reflection to access the protected method
    $method = new ReflectionMethod($testInstance, 'prepareUrlForRequest');
    $method->setAccessible(true);

    // Test relative URL
    $result = $method->invoke($testInstance, '/api/test');
    expect($result)->toBe('http://localhost/api/test');

    // Test absolute URL
    $result = $method->invoke($testInstance, 'http://example.com/test');
    expect($result)->toBe('http://example.com/test');

    // Test URL without leading slash
    $result = $method->invoke($testInstance, 'api/test');
    expect($result)->toBe('http://localhost/api/test');
});

it('formats to expected json', function () {
    $testInstance = createHttpRequestsTestInstance();

    // Use reflection to access the protected method
    $method = new ReflectionMethod($testInstance, 'formatToExpectedJson');
    $method->setAccessible(true);

    $result = $method->invoke($testInstance, 'name', 'John');
    expect($result)->toBe('"name":"John"');

    $result = $method->invoke($testInstance, 'age', 25);
    expect($result)->toBe('"age":25');

    $result = $method->invoke($testInstance, 'active', true);
    expect($result)->toBe('"active":true');
});

it('tests should return json method', function () {
    $testInstance = createHttpRequestsTestInstance();

    // Use reflection to access the protected method
    $method = new ReflectionMethod($testInstance, 'shouldReturnJson');
    $method->setAccessible(true);

    // Mock a response for the seeJson call
    $mockResponse = m::mock('Illuminate\Testing\TestResponse');
    $mockResponse->shouldReceive('assertJsonFragment')->once()->with(['test' => 'data']);
    setResponseProperty($testInstance, $mockResponse);

    // Test that shouldReturnJson calls receiveJson
    $result = $method->invoke($testInstance, ['test' => 'data']);
    expect($result)->toBe($testInstance);
});

it('tests receive json method', function () {
    $testInstance = createHttpRequestsTestInstance();

    // Mock a response for the seeJson call
    $mockResponse = m::mock('Illuminate\Testing\TestResponse');
    $mockResponse->shouldReceive('assertJsonFragment')->once()->with(['test' => 'data']);
    setResponseProperty($testInstance, $mockResponse);

    // Test that receiveJson calls seeJson
    $result = $testInstance->callReceiveJson(['test' => 'data']);
    expect($result)->toBe($testInstance);
});

it('tests see json contains method', function () {
    $testInstance = createHttpRequestsTestInstance();

    // Use reflection to access the protected method
    $method = new ReflectionMethod($testInstance, 'seeJsonContains');
    $method->setAccessible(true);

    // Mock a response with JSON content
    $mockResponse = m::mock('Illuminate\Testing\TestResponse');
    $mockResponse->shouldReceive('assertJsonFragment')->once()->with(['test' => 'data']);

    // Use reflection to set the protected response property
    $responseProperty = new ReflectionProperty($testInstance, 'response');
    $responseProperty->setAccessible(true);
    $responseProperty->setValue($testInstance, $mockResponse);

    $result = $method->invoke($testInstance, ['test' => 'data'], false);
    expect($result)->toBe($testInstance);
});

it('tests see json doesnt contains method', function () {
    $testInstance = createHttpRequestsTestInstance();

    // Use reflection to access the protected method
    $method = new ReflectionMethod($testInstance, 'seeJsonDoesntContains');
    $method->setAccessible(true);

    // Mock a response with JSON content
    $mockResponse = m::mock('Illuminate\Testing\TestResponse');
    $mockResponse->shouldReceive('assertJsonMissing')->once()->with(['test' => 'data'], false);
    setResponseProperty($testInstance, $mockResponse);

    $result = $method->invoke($testInstance, ['test' => 'data']);
    expect($result)->toBe($testInstance);
});

it('tests see status code method', function () {
    $testInstance = createHttpRequestsTestInstance();

    // Use reflection to access the protected method
    $method = new ReflectionMethod($testInstance, 'seeStatusCode');
    $method->setAccessible(true);

    // Mock a response
    $mockResponse = m::mock('Illuminate\Testing\TestResponse');
    $mockResponse->shouldReceive('assertStatus')->once()->with(200);
    setResponseProperty($testInstance, $mockResponse);

    $result = $method->invoke($testInstance, 200);
    expect($result)->toBe($testInstance);
});

it('tests see header method', function () {
    $testInstance = createHttpRequestsTestInstance();

    // Use reflection to access the protected method
    $method = new ReflectionMethod($testInstance, 'seeHeader');
    $method->setAccessible(true);

    // Mock a response
    $mockResponse = m::mock('Illuminate\Testing\TestResponse');
    $mockResponse->shouldReceive('assertHeader')->once()->with('Content-Type', 'application/json');
    setResponseProperty($testInstance, $mockResponse);

    $result = $method->invoke($testInstance, 'Content-Type', 'application/json');
    expect($result)->toBe($testInstance);
});

it('tests assert response ok method', function () {
    $testInstance = createHttpRequestsTestInstance();

    // Mock a response
    $mockResponse = m::mock('Illuminate\Testing\TestResponse');
    $mockResponse->shouldReceive('assertOk')->once();
    setResponseProperty($testInstance, $mockResponse);

    $testInstance->assertResponseOk();
    expect(true)->toBeTrue(); // If we get here, the method was called successfully
});

it('tests assert response status method', function () {
    $testInstance = createHttpRequestsTestInstance();

    // Mock a response
    $mockResponse = m::mock('Illuminate\Testing\TestResponse');
    $mockResponse->shouldReceive('assertStatus')->once()->with(201);
    setResponseProperty($testInstance, $mockResponse);

    $testInstance->assertResponseStatus(201);
    expect(true)->toBeTrue(); // If we get here, the method was called successfully
});

it('tests see json structure method', function () {
    $testInstance = createHttpRequestsTestInstance();

    // Mock a response
    $mockResponse = m::mock('Illuminate\Testing\TestResponse');
    $structure = ['name', 'email'];
    $mockResponse->shouldReceive('assertJsonStructure')->once()->with($structure, null);
    setResponseProperty($testInstance, $mockResponse);

    $result = $testInstance->seeJsonStructure($structure);
    expect($result)->toBe($testInstance);
});

it('tests see json equals method', function () {
    $testInstance = createHttpRequestsTestInstance();

    // Mock a response with JSON content
    $mockResponse = m::mock('Illuminate\Testing\TestResponse');
    $jsonData = ['name' => 'John', 'age' => 30];
    $mockResponse->shouldReceive('getContent')->once()->andReturn(json_encode($jsonData));
    setResponseProperty($testInstance, $mockResponse);

    $result = $testInstance->seeJsonEquals($jsonData);
    expect($result)->toBe($testInstance);
});

it('tests dont see json method', function () {
    $testInstance = createHttpRequestsTestInstance();

    // Mock a response
    $mockResponse = m::mock('Illuminate\Testing\TestResponse');
    $mockResponse->shouldReceive('assertJsonMissing')->once()->with(['test' => 'data'], false);
    setResponseProperty($testInstance, $mockResponse);

    $result = $testInstance->dontSeeJson(['test' => 'data']);
    expect($result)->toBe($testInstance);
});

it('tests without middleware method', function () {
    $testInstance = createHttpRequestsTestInstance();

    // Mock the app
    $mockApp = m::mock();
    $mockApp->shouldReceive('instance')->once()->with('middleware.disable', true);
    $testInstance->app = $mockApp;

    $result = $testInstance->withoutMiddleware();
    expect($result)->toBe($testInstance);
});

it('tests json method exists', function () {
    $testInstance = createHttpRequestsTestInstance();

    // Test that the json method exists and can be called
    expect(method_exists($testInstance, 'json'))->toBeTrue();
});

it('tests get method exists', function () {
    $testInstance = createHttpRequestsTestInstance();

    // Test that the get method exists
    expect(method_exists($testInstance, 'get'))->toBeTrue();
});

it('tests post method exists', function () {
    $testInstance = createHttpRequestsTestInstance();

    // Test that the post method exists
    expect(method_exists($testInstance, 'post'))->toBeTrue();
});

it('tests put method', function () {
    $testInstance = createHttpRequestsTestInstance();

    // Test that the put method exists
    expect(method_exists($testInstance, 'put'))->toBeTrue();
});

it('tests patch method', function () {
    $testInstance = createHttpRequestsTestInstance();

    // Test that the patch method exists
    expect(method_exists($testInstance, 'patch'))->toBeTrue();
});

it('tests delete method', function () {
    $testInstance = createHttpRequestsTestInstance();

    // Test that the delete method exists
    expect(method_exists($testInstance, 'delete'))->toBeTrue();
});

it('tests head method', function () {
    $testInstance = createHttpRequestsTestInstance();

    // Test that the head method exists
    expect(method_exists($testInstance, 'head'))->toBeTrue();
});

it('tests handle method exists', function () {
    $testInstance = createHttpRequestsTestInstance();

    // Test that the handle method exists
    expect(method_exists($testInstance, 'handle'))->toBeTrue();
});

it('tests seeJson with null data', function () {
    $testInstance = createHttpRequestsTestInstance();

    // Mock a response with valid JSON content
    $mockResponse = m::mock('Illuminate\Testing\TestResponse');
    $mockResponse->shouldReceive('getContent')->andReturn('{"test": "data"}');
    $mockResponse->shouldReceive('assertJsonFragment')->once()->with(['test' => 'data']);

    setResponseProperty($testInstance, $mockResponse);

    $result = $testInstance->seeJson(null);
    expect($result)->toBe($testInstance);
});

it('tests seeJson with invalid json response', function () {
    $testInstance = createHttpRequestsTestInstance();

    // Mock a response with invalid JSON content
    $mockResponse = m::mock('Illuminate\Testing\TestResponse');
    $mockResponse->shouldReceive('getContent')->andReturn('invalid json');

    // Set currentUri for the error message
    $uriProperty = new ReflectionProperty($testInstance, 'currentUri');
    $uriProperty->setAccessible(true);
    $uriProperty->setValue($testInstance, 'http://localhost/test');

    setResponseProperty($testInstance, $mockResponse);

    // This should throw an exception
    expect(function () use ($testInstance) {
        $testInstance->seeJson(null);
    })->toThrow(\PHPUnit\Framework\AssertionFailedError::class);
});

it('tests json method with data and headers', function () {
    $testInstance = createHttpRequestsTestInstance();

    // Mock the app with ArrayAccess support
    $mockApp = m::mock('ArrayAccess');
    $mockRequest = m::mock('Laravel\Lumen\Http\Request');
    $mockResponse = m::mock('Illuminate\Http\Response');

    $mockApp->shouldReceive('offsetSet')->with('request', m::type('Laravel\Lumen\Http\Request'));
    $mockApp->shouldReceive('offsetGet')->with('request')->andReturn($mockRequest);
    $mockApp->shouldReceive('prepareResponse')->andReturn($mockResponse);
    $mockApp->shouldReceive('handle')->andReturn($mockResponse);

    $testInstance->setApp($mockApp);

    $data = ['name' => 'John', 'email' => 'john@example.com'];
    $headers = ['X-Custom-Header' => 'test'];

    $result = $testInstance->json('POST', '/api/users', $data, $headers);

    expect($result)->toBe($testInstance);
});

it('tests call method with all parameters', function () {
    $testInstance = createHttpRequestsTestInstance();

    // Mock the app with ArrayAccess support
    $mockApp = m::mock('ArrayAccess');
    $mockRequest = m::mock('Laravel\Lumen\Http\Request');
    $mockResponse = m::mock('Illuminate\Http\Response');

    $mockApp->shouldReceive('offsetSet')->with('request', m::type('Laravel\Lumen\Http\Request'));
    $mockApp->shouldReceive('offsetGet')->with('request')->andReturn($mockRequest);
    $mockApp->shouldReceive('prepareResponse')->andReturn($mockResponse);
    $mockApp->shouldReceive('handle')->andReturn($mockResponse);

    $testInstance->setApp($mockApp);
    $testInstance->baseUrl = 'http://localhost';

    $result = $testInstance->call('POST', '/api/test', ['param' => 'value'], ['cookie' => 'value'], [], ['HTTP_ACCEPT' => 'application/json'], 'content');

    expect($result)->toBeInstanceOf('Illuminate\Testing\TestResponse');
});

it('tests handle method with request', function () {
    $testInstance = createHttpRequestsTestInstance();

    // Mock the app and request
    $mockApp = m::mock();
    $mockRequest = m::mock('Illuminate\Http\Request');
    $mockResponse = m::mock('Illuminate\Http\Response');

    $mockRequest->shouldReceive('fullUrl')->andReturn('http://localhost/test');
    $mockApp->shouldReceive('handle')->with($mockRequest)->andReturn($mockResponse);
    $mockApp->shouldReceive('prepareResponse')->with($mockResponse)->andReturn($mockResponse);

    $testInstance->setApp($mockApp);

    $result = $testInstance->handle($mockRequest);

    expect($result)->toBe($testInstance);
});

it('tests shouldReturnJson method', function () {
    $testInstance = createHttpRequestsTestInstance();

    // Mock a response with valid JSON content
    $mockResponse = m::mock('Illuminate\Testing\TestResponse');
    $mockResponse->shouldReceive('getContent')->andReturn('{"test": "data"}');
    $mockResponse->shouldReceive('assertJsonFragment')->once()->with(['test' => 'data']);

    setResponseProperty($testInstance, $mockResponse);

    // Use reflection to access the protected method
    $method = new ReflectionMethod($testInstance, 'shouldReturnJson');
    $method->setAccessible(true);

    $result = $method->invoke($testInstance, null);
    expect($result)->toBe($testInstance);
});

it('tests receiveJson method', function () {
    $testInstance = createHttpRequestsTestInstance();

    // Mock a response with valid JSON content
    $mockResponse = m::mock('Illuminate\Testing\TestResponse');
    $mockResponse->shouldReceive('getContent')->andReturn('{"test": "data"}');
    $mockResponse->shouldReceive('assertJsonFragment')->once()->with(['test' => 'data']);

    setResponseProperty($testInstance, $mockResponse);

    $result = $testInstance->callReceiveJson(null);
    expect($result)->toBe($testInstance);
});

it('tests seeJsonContains method', function () {
    $testInstance = createHttpRequestsTestInstance();

    // Mock a response
    $mockResponse = m::mock('Illuminate\Testing\TestResponse');
    $mockResponse->shouldReceive('assertJsonFragment')->once()->with(['name' => 'John']);

    setResponseProperty($testInstance, $mockResponse);

    // Use reflection to access the protected method
    $method = new ReflectionMethod($testInstance, 'seeJsonContains');
    $method->setAccessible(true);

    $result = $method->invoke($testInstance, ['name' => 'John'], false);
    expect($result)->toBe($testInstance);
});

it('tests seeJsonContains method with negate', function () {
    $testInstance = createHttpRequestsTestInstance();

    // Mock a response
    $mockResponse = m::mock('Illuminate\Testing\TestResponse');
    $mockResponse->shouldReceive('assertJsonMissing')->once()->with(['name' => 'John'], false);

    setResponseProperty($testInstance, $mockResponse);

    // Use reflection to access the protected method
    $method = new ReflectionMethod($testInstance, 'seeJsonContains');
    $method->setAccessible(true);

    $result = $method->invoke($testInstance, ['name' => 'John'], true);
    expect($result)->toBe($testInstance);
});

it('tests seeJsonDoesntContains method', function () {
    $testInstance = createHttpRequestsTestInstance();

    // Mock a response
    $mockResponse = m::mock('Illuminate\Testing\TestResponse');
    $mockResponse->shouldReceive('assertJsonMissing')->once()->with(['name' => 'John'], false);

    setResponseProperty($testInstance, $mockResponse);

    // Use reflection to access the protected method
    $method = new ReflectionMethod($testInstance, 'seeJsonDoesntContains');
    $method->setAccessible(true);

    $result = $method->invoke($testInstance, ['name' => 'John']);
    expect($result)->toBe($testInstance);
});
