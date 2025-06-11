<?php

declare(strict_types=1);

use Illuminate\Contracts\Events\Dispatcher;
use Laravel\Lumen\Providers\EventServiceProvider;
use Mockery as m;

beforeEach(function () {
    $this->app = app();
    $this->events = m::mock(Dispatcher::class);
    $this->app->instance('events', $this->events);
});

afterEach(function () {
    m::close();
});

it('registers service provider', function () {
    $provider = new EventServiceProvider($this->app);
    
    expect($provider)->toBeInstanceOf(EventServiceProvider::class);
    
    $provider->register();
});

it('boots with empty listeners and subscribers', function () {
    $provider = new EventServiceProvider($this->app);
    
    $provider->boot();
    
    expect($provider->listens())->toBe([]);
});

it('boots with event listeners', function () {
    $provider = new class($this->app) extends EventServiceProvider {
        protected $listen = [
            'test.event' => ['TestListener'],
            'another.event' => ['AnotherListener', 'SecondListener'],
        ];
    };
    
    $this->events->shouldReceive('listen')->with('test.event', 'TestListener')->once();
    $this->events->shouldReceive('listen')->with('another.event', 'AnotherListener')->once();
    $this->events->shouldReceive('listen')->with('another.event', 'SecondListener')->once();
    
    $provider->boot();
    
    expect($provider->listens())->toBe([
        'test.event' => ['TestListener'],
        'another.event' => ['AnotherListener', 'SecondListener'],
    ]);
});

it('boots with event subscribers', function () {
    $provider = new class($this->app) extends EventServiceProvider {
        protected $subscribe = ['TestSubscriber', 'AnotherSubscriber'];
    };
    
    $this->events->shouldReceive('subscribe')->with('TestSubscriber')->once();
    $this->events->shouldReceive('subscribe')->with('AnotherSubscriber')->once();
    
    $provider->boot();
});

it('boots with both listeners and subscribers', function () {
    $provider = new class($this->app) extends EventServiceProvider {
        protected $listen = [
            'test.event' => ['TestListener'],
        ];
        protected $subscribe = ['TestSubscriber'];
    };
    
    $this->events->shouldReceive('listen')->with('test.event', 'TestListener')->once();
    $this->events->shouldReceive('subscribe')->with('TestSubscriber')->once();
    
    $provider->boot();
});
