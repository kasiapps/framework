<?php

namespace Kasi\Tests\Queue;

use Kasi\Container\Container;
use Kasi\Contracts\Redis\Factory;
use Kasi\Queue\LuaScripts;
use Kasi\Queue\Queue;
use Kasi\Queue\RedisQueue;
use Kasi\Support\Carbon;
use Kasi\Support\Str;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class QueueRedisQueueTest extends TestCase
{
    protected function tearDown(): void
    {
        m::close();
    }

    public function testPushProperlyPushesJobOntoRedis()
    {
        $uuid = Str::uuid();

        Str::createUuidsUsing(function () use ($uuid) {
            return $uuid;
        });

        $queue = $this->getMockBuilder(RedisQueue::class)->onlyMethods(['getRandomId'])->setConstructorArgs([$redis = m::mock(Factory::class), 'default'])->getMock();
        $queue->expects($this->once())->method('getRandomId')->willReturn('foo');
        $queue->setContainer($container = m::spy(Container::class));
        $redis->shouldReceive('connection')->once()->andReturn($redis);
        $redis->shouldReceive('eval')->once()->with(LuaScripts::push(), 2, 'queues:default', 'queues:default:notify', json_encode(['uuid' => $uuid, 'displayName' => 'foo', 'job' => 'foo', 'maxTries' => null, 'maxExceptions' => null, 'failOnTimeout' => false, 'backoff' => null, 'timeout' => null, 'data' => ['data'], 'id' => 'foo', 'attempts' => 0]));

        $id = $queue->push('foo', ['data']);
        $this->assertSame('foo', $id);
        $container->shouldHaveReceived('bound')->with('events')->twice();

        Str::createUuidsNormally();
    }

    public function testPushProperlyPushesJobOntoRedisWithCustomPayloadHook()
    {
        $uuid = Str::uuid();

        Str::createUuidsUsing(function () use ($uuid) {
            return $uuid;
        });

        $queue = $this->getMockBuilder(RedisQueue::class)->onlyMethods(['getRandomId'])->setConstructorArgs([$redis = m::mock(Factory::class), 'default'])->getMock();
        $queue->expects($this->once())->method('getRandomId')->willReturn('foo');
        $queue->setContainer($container = m::spy(Container::class));
        $redis->shouldReceive('connection')->once()->andReturn($redis);
        $redis->shouldReceive('eval')->once()->with(LuaScripts::push(), 2, 'queues:default', 'queues:default:notify', json_encode(['uuid' => $uuid, 'displayName' => 'foo', 'job' => 'foo', 'maxTries' => null, 'maxExceptions' => null, 'failOnTimeout' => false, 'backoff' => null, 'timeout' => null, 'data' => ['data'], 'custom' => 'taylor', 'id' => 'foo', 'attempts' => 0]));

        Queue::createPayloadUsing(function ($connection, $queue, $payload) {
            return ['custom' => 'taylor'];
        });

        $id = $queue->push('foo', ['data']);
        $this->assertSame('foo', $id);
        $container->shouldHaveReceived('bound')->with('events')->twice();

        Queue::createPayloadUsing(null);

        Str::createUuidsNormally();
    }

    public function testPushProperlyPushesJobOntoRedisWithTwoCustomPayloadHook()
    {
        $uuid = Str::uuid();

        Str::createUuidsUsing(function () use ($uuid) {
            return $uuid;
        });

        $queue = $this->getMockBuilder(RedisQueue::class)->onlyMethods(['getRandomId'])->setConstructorArgs([$redis = m::mock(Factory::class), 'default'])->getMock();
        $queue->expects($this->once())->method('getRandomId')->willReturn('foo');
        $queue->setContainer($container = m::spy(Container::class));
        $redis->shouldReceive('connection')->once()->andReturn($redis);
        $redis->shouldReceive('eval')->once()->with(LuaScripts::push(), 2, 'queues:default', 'queues:default:notify', json_encode(['uuid' => $uuid, 'displayName' => 'foo', 'job' => 'foo', 'maxTries' => null, 'maxExceptions' => null, 'failOnTimeout' => false, 'backoff' => null, 'timeout' => null, 'data' => ['data'], 'custom' => 'taylor', 'bar' => 'foo', 'id' => 'foo', 'attempts' => 0]));

        Queue::createPayloadUsing(function ($connection, $queue, $payload) {
            return ['custom' => 'taylor'];
        });

        Queue::createPayloadUsing(function ($connection, $queue, $payload) {
            return ['bar' => 'foo'];
        });

        $id = $queue->push('foo', ['data']);
        $this->assertSame('foo', $id);
        $container->shouldHaveReceived('bound')->with('events')->twice();

        Queue::createPayloadUsing(null);

        Str::createUuidsNormally();
    }

    public function testDelayedPushProperlyPushesJobOntoRedis()
    {
        $uuid = Str::uuid();

        Str::createUuidsUsing(function () use ($uuid) {
            return $uuid;
        });

        $queue = $this->getMockBuilder(RedisQueue::class)->onlyMethods(['availableAt', 'getRandomId'])->setConstructorArgs([$redis = m::mock(Factory::class), 'default'])->getMock();
        $queue->setContainer($container = m::spy(Container::class));
        $queue->expects($this->once())->method('getRandomId')->willReturn('foo');
        $queue->expects($this->once())->method('availableAt')->with(1)->willReturn(2);

        $redis->shouldReceive('connection')->once()->andReturn($redis);
        $redis->shouldReceive('zadd')->once()->with(
            'queues:default:delayed',
            2,
            json_encode(['uuid' => $uuid, 'displayName' => 'foo', 'job' => 'foo', 'maxTries' => null, 'maxExceptions' => null, 'failOnTimeout' => false, 'backoff' => null, 'timeout' => null, 'data' => ['data'], 'id' => 'foo', 'attempts' => 0])
        );

        $id = $queue->later(1, 'foo', ['data']);
        $this->assertSame('foo', $id);
        $container->shouldHaveReceived('bound')->with('events')->twice();

        Str::createUuidsNormally();
    }

    public function testDelayedPushWithDateTimeProperlyPushesJobOntoRedis()
    {
        $uuid = Str::uuid();

        Str::createUuidsUsing(function () use ($uuid) {
            return $uuid;
        });

        $date = Carbon::now();
        $queue = $this->getMockBuilder(RedisQueue::class)->onlyMethods(['availableAt', 'getRandomId'])->setConstructorArgs([$redis = m::mock(Factory::class), 'default'])->getMock();
        $queue->setContainer($container = m::spy(Container::class));
        $queue->expects($this->once())->method('getRandomId')->willReturn('foo');
        $queue->expects($this->once())->method('availableAt')->with($date)->willReturn(2);

        $redis->shouldReceive('connection')->once()->andReturn($redis);
        $redis->shouldReceive('zadd')->once()->with(
            'queues:default:delayed',
            2,
            json_encode(['uuid' => $uuid, 'displayName' => 'foo', 'job' => 'foo', 'maxTries' => null, 'maxExceptions' => null, 'failOnTimeout' => false, 'backoff' => null, 'timeout' => null, 'data' => ['data'], 'id' => 'foo', 'attempts' => 0])
        );

        $queue->later($date, 'foo', ['data']);
        $container->shouldHaveReceived('bound')->with('events')->twice();

        Str::createUuidsNormally();
    }
}
