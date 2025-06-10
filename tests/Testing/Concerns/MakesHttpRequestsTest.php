<?php

declare(strict_types=1);

use Laravel\Lumen\Testing\Concerns\MakesHttpRequests;

// Helper function to create a test instance with the MakesHttpRequests trait
function createHttpRequestsTestInstance()
{
    return new class {
        use MakesHttpRequests;

        public $app;

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
