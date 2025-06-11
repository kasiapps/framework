<?php

declare(strict_types=1);

use Laravel\Lumen\Testing\DatabaseMigrations;

it('runs database migrations', function () {
    $mock = new class {
        use DatabaseMigrations;
        
        public $artisanCalls = [];
        public $beforeApplicationDestroyedCallbacks = [];
        
        public function artisan($command, $parameters = [])
        {
            $this->artisanCalls[] = [$command, $parameters];
            return 0;
        }
        
        public function beforeApplicationDestroyed(callable $callback)
        {
            $this->beforeApplicationDestroyedCallbacks[] = $callback;
        }
    };
    
    $mock->runDatabaseMigrations();
    
    expect($mock->artisanCalls)->toHaveCount(1);
    expect($mock->artisanCalls[0])->toBe(['migrate:fresh', []]);
    expect($mock->beforeApplicationDestroyedCallbacks)->toHaveCount(1);
    
    // Test the callback
    $callback = $mock->beforeApplicationDestroyedCallbacks[0];
    $callback();
    
    expect($mock->artisanCalls)->toHaveCount(2);
    expect($mock->artisanCalls[1])->toBe(['migrate:rollback', []]);
});
