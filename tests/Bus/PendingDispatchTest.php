<?php

declare(strict_types=1);

use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Laravel\Lumen\Bus\PendingDispatch;
use Mockery as m;

beforeEach(function () {
  $this->dispatcher = m::mock(Dispatcher::class);
  app()->instance(Dispatcher::class, $this->dispatcher);
});

afterEach(function () {
  m::close();
});

it('sets connection on job', function () {
  $job = m::mock();
  $job->shouldReceive('onConnection')->with('redis')->once()->andReturnSelf();

  $this->dispatcher->shouldReceive('dispatch')->with($job)->once();

  $pendingDispatch = new PendingDispatch($job);
  $result = $pendingDispatch->onConnection('redis');

  expect($result)->toBe($pendingDispatch);
});

it('sets queue on job', function () {
  $job = m::mock();
  $job->shouldReceive('onQueue')->with('high')->once()->andReturnSelf();

  $this->dispatcher->shouldReceive('dispatch')->with($job)->once();

  $pendingDispatch = new PendingDispatch($job);
  $result = $pendingDispatch->onQueue('high');

  expect($result)->toBe($pendingDispatch);
});

it('dispatches regular job on destruct', function () {
  $job = m::mock();

  $this->dispatcher->shouldReceive('dispatch')->with($job)->once();

  $pendingDispatch = new PendingDispatch($job);
  unset($pendingDispatch);
});

it('dispatches unique job when lock acquired', function () {
  $job = new class implements ShouldBeUnique
  {
    public $uniqueId = 'test-id';

    public $uniqueFor = 60;
  };

  $cache = m::mock(Cache::class);
  $lock = m::mock();

  app()->instance(Cache::class, $cache);

  $cache->shouldReceive('lock')
    ->with('laravel_unique_job:'.get_class($job).'test-id', 60)
    ->once()
    ->andReturn($lock);

  $lock->shouldReceive('get')->once()->andReturn(true);

  $this->dispatcher->shouldReceive('dispatch')->with($job)->once();

  $pendingDispatch = new PendingDispatch($job);
  unset($pendingDispatch);
});

it('does not dispatch unique job when lock not acquired', function () {
  $job = new class implements ShouldBeUnique
  {
    public $uniqueId = 'test-id';

    public $uniqueFor = 60;
  };

  $cache = m::mock(Cache::class);
  $lock = m::mock();

  app()->instance(Cache::class, $cache);

  $cache->shouldReceive('lock')
    ->with('laravel_unique_job:'.get_class($job).'test-id', 60)
    ->once()
    ->andReturn($lock);

  $lock->shouldReceive('get')->once()->andReturn(false);

  $this->dispatcher->shouldNotReceive('dispatch');

  $pendingDispatch = new PendingDispatch($job);
  unset($pendingDispatch);
});

it('dispatches unique job with uniqueId method', function () {
  // Test line 65: $this->job->uniqueId()
  $job = new class implements ShouldBeUnique
  {
    public $uniqueFor = 60;

    public function uniqueId(): string
    {
      return 'method-generated-id';
    }
  };

  $cache = m::mock(Cache::class);
  $lock = m::mock();

  app()->instance(Cache::class, $cache);

  $cache->shouldReceive('lock')
    ->with('laravel_unique_job:'.get_class($job).'method-generated-id', 60)
    ->once()
    ->andReturn($lock);

  $lock->shouldReceive('get')->once()->andReturn(true);

  $this->dispatcher->shouldReceive('dispatch')->with($job)->once();

  $pendingDispatch = new PendingDispatch($job);
  unset($pendingDispatch);
});

it('dispatches unique job with uniqueVia method', function () {
  // Test line 69: $this->job->uniqueVia()
  $customCache = m::mock(Cache::class);

  $job = new class($customCache) implements ShouldBeUnique
  {
    public $uniqueId = 'test-id';
    public $uniqueFor = 60;

    public function __construct(private $customCache) {}

    public function uniqueVia()
    {
      return $this->customCache;
    }
  };

  $lock = m::mock();

  $customCache->shouldReceive('lock')
    ->with('laravel_unique_job:'.get_class($job).'test-id', 60)
    ->once()
    ->andReturn($lock);

  $lock->shouldReceive('get')->once()->andReturn(true);

  $this->dispatcher->shouldReceive('dispatch')->with($job)->once();

  $pendingDispatch = new PendingDispatch($job);
  unset($pendingDispatch);
});
