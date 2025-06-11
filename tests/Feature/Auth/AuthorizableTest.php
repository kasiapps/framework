<?php

declare(strict_types=1);

use Illuminate\Contracts\Auth\Access\Gate;
use Laravel\Lumen\Auth\Authorizable;
use Mockery as m;

beforeEach(function () {
    $this->gate = m::mock(Gate::class);
    app()->instance(Gate::class, $this->gate);
});

afterEach(function () {
    m::close();
});

it('can check ability', function () {
    $user = new class {
        use Authorizable;
    };
    
    $this->gate->shouldReceive('forUser')->with($user)->andReturnSelf();
    $this->gate->shouldReceive('check')->with('test-ability', [])->andReturn(true);
    
    expect($user->can('test-ability'))->toBeTrue();
});

it('can check ability with arguments', function () {
    $user = new class {
        use Authorizable;
    };
    
    $arguments = ['arg1', 'arg2'];
    $this->gate->shouldReceive('forUser')->with($user)->andReturnSelf();
    $this->gate->shouldReceive('check')->with('test-ability', $arguments)->andReturn(true);
    
    expect($user->can('test-ability', $arguments))->toBeTrue();
});

it('cant check ability', function () {
    $user = new class {
        use Authorizable;
    };
    
    $this->gate->shouldReceive('forUser')->with($user)->andReturnSelf();
    $this->gate->shouldReceive('check')->with('test-ability', [])->andReturn(false);
    
    expect($user->cant('test-ability'))->toBeTrue();
});

it('cant check ability with arguments', function () {
    $user = new class {
        use Authorizable;
    };
    
    $arguments = ['arg1', 'arg2'];
    $this->gate->shouldReceive('forUser')->with($user)->andReturnSelf();
    $this->gate->shouldReceive('check')->with('test-ability', $arguments)->andReturn(false);
    
    expect($user->cant('test-ability', $arguments))->toBeTrue();
});

it('cannot check ability', function () {
    $user = new class {
        use Authorizable;
    };
    
    $this->gate->shouldReceive('forUser')->with($user)->andReturnSelf();
    $this->gate->shouldReceive('check')->with('test-ability', [])->andReturn(false);
    
    expect($user->cannot('test-ability'))->toBeTrue();
});

it('cannot check ability with arguments', function () {
    $user = new class {
        use Authorizable;
    };
    
    $arguments = ['arg1', 'arg2'];
    $this->gate->shouldReceive('forUser')->with($user)->andReturnSelf();
    $this->gate->shouldReceive('check')->with('test-ability', $arguments)->andReturn(false);
    
    expect($user->cannot('test-ability', $arguments))->toBeTrue();
});
