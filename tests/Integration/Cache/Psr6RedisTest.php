<?php

namespace Kasi\Tests\Integration\Cache;

use Kasi\Foundation\Testing\Concerns\InteractsWithRedis;
use Kasi\Support\Facades\Cache;
use Kasi\Tests\Integration\Cache\Fixtures\Unserializable;
use Orchestra\Testbench\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class Psr6RedisTest extends TestCase
{
    use InteractsWithRedis;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpRedis();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->tearDownRedis();
    }

    #[DataProvider('redisClientDataProvider')]
    public function testTransactionIsNotOpenedWhenSerializationFails($redisClient): void
    {
        $this->app['config']['cache.default'] = 'redis';
        $this->app['config']['database.redis.client'] = $redisClient;

        $cache = $this->app->make('cache.psr6');

        $item = $cache->getItem('foo');

        $item->set(new Unserializable());
        $item->expiresAfter(60);

        $cache->save($item);

        Cache::store('redis')->get('foo');
    }

    /**
     * @return array
     */
    public static function redisClientDataProvider(): array
    {
        return [
            ['predis'],
            ['phpredis'],
        ];
    }
}
