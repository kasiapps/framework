<?php

declare(strict_types=1);

use Laravel\Lumen\Testing\DatabaseTransactions;
use Mockery as m;

beforeEach(function () {
    $this->connection = m::mock();
    $this->database = m::mock();
    $this->app = m::mock();
    
    $this->app->shouldReceive('make')->with('db')->andReturn($this->database);
});

afterEach(function () {
    m::close();
});

it('begins database transaction with default connection', function () {
    $mock = new class {
        use DatabaseTransactions;
        
        public $app;
        public $beforeApplicationDestroyedCallbacks = [];
        
        public function beforeApplicationDestroyed(callable $callback)
        {
            $this->beforeApplicationDestroyedCallbacks[] = $callback;
        }
    };
    
    $mock->app = $this->app;
    
    $this->database->shouldReceive('connection')->with(null)->andReturn($this->connection);
    $this->connection->shouldReceive('beginTransaction')->once();
    
    $mock->beginDatabaseTransaction();
    
    expect($mock->beforeApplicationDestroyedCallbacks)->toHaveCount(1);
});

it('begins database transaction with custom connections', function () {
    $mock = new class {
        use DatabaseTransactions;
        
        public $app;
        public $beforeApplicationDestroyedCallbacks = [];
        protected $connectionsToTransact = ['mysql', 'sqlite'];
        
        public function beforeApplicationDestroyed(callable $callback)
        {
            $this->beforeApplicationDestroyedCallbacks[] = $callback;
        }
    };
    
    $mock->app = $this->app;
    
    $connection1 = m::mock();
    $connection2 = m::mock();
    
    $this->database->shouldReceive('connection')->with('mysql')->andReturn($connection1);
    $this->database->shouldReceive('connection')->with('sqlite')->andReturn($connection2);
    
    $connection1->shouldReceive('beginTransaction')->once();
    $connection2->shouldReceive('beginTransaction')->once();
    
    $mock->beginDatabaseTransaction();
    
    expect($mock->beforeApplicationDestroyedCallbacks)->toHaveCount(1);
});

it('rolls back transactions on application destroyed', function () {
    $mock = new class {
        use DatabaseTransactions;
        
        public $app;
        public $beforeApplicationDestroyedCallbacks = [];
        protected $connectionsToTransact = ['mysql'];
        
        public function beforeApplicationDestroyed(callable $callback)
        {
            $this->beforeApplicationDestroyedCallbacks[] = $callback;
        }
    };
    
    $mock->app = $this->app;
    
    $this->database->shouldReceive('connection')->with('mysql')->andReturn($this->connection);
    $this->connection->shouldReceive('beginTransaction')->once();
    
    $mock->beginDatabaseTransaction();
    
    // Test the callback
    $this->database->shouldReceive('connection')->with('mysql')->andReturn($this->connection);
    $this->connection->shouldReceive('rollBack')->once();
    $this->connection->shouldReceive('disconnect')->once();
    
    $callback = $mock->beforeApplicationDestroyedCallbacks[0];
    $callback();
});
