<?php

declare(strict_types=1);

use Laravel\Lumen\Testing\WithoutMiddleware;

it('disables middleware for all tests when trait method exists', function () {
    $mock = new class {
        use WithoutMiddleware;
        
        public $withoutMiddlewareCalled = false;
        
        public function withoutMiddleware()
        {
            $this->withoutMiddlewareCalled = true;
        }
    };
    
    $mock->disableMiddlewareForAllTests();
    
    expect($mock->withoutMiddlewareCalled)->toBeTrue();
});

it('throws exception when withoutMiddleware method does not exist', function () {
    $mock = new class {
        use WithoutMiddleware;
    };
    
    expect(fn () => $mock->disableMiddlewareForAllTests())
        ->toThrow(Exception::class, 'Unable to disable middleware. MakesHttpRequests trait not used.');
});
