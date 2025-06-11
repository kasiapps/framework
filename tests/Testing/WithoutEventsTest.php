<?php

declare(strict_types=1);

use Laravel\Lumen\Testing\WithoutEvents;

it('disables events for all tests when trait method exists', function () {
    $mock = new class {
        use WithoutEvents;
        
        public $withoutEventsCalled = false;
        
        public function withoutEvents()
        {
            $this->withoutEventsCalled = true;
        }
    };
    
    $mock->disableEventsForAllTests();
    
    expect($mock->withoutEventsCalled)->toBeTrue();
});

it('throws exception when withoutEvents method does not exist', function () {
    $mock = new class {
        use WithoutEvents;
    };
    
    expect(fn () => $mock->disableEventsForAllTests())
        ->toThrow(Exception::class, 'Unable to disable events. ApplicationTrait not used.');
});
