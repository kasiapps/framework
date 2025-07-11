<?php

namespace Kasi\Tests\Queue;

use Kasi\Container\Container;
use Kasi\Contracts\Events\Dispatcher;
use Kasi\Foundation\Testing\Concerns\InteractsWithRedis;
use Kasi\Queue\Events\JobQueued;
use Kasi\Queue\Events\JobQueueing;
use Kasi\Queue\Jobs\RedisJob;
use Kasi\Queue\RedisQueue;
use Kasi\Support\InteractsWithTime;
use Kasi\Support\Str;
use Mockery as m;
use Orchestra\Testbench\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;

#[RequiresPhpExtension('redis')]
class RedisQueueTest extends TestCase
{
    use InteractsWithRedis, InteractsWithTime;

    /**
     * @var \Kasi\Queue\RedisQueue
     */
    private $queue;

    /**
     * @var \Mockery\MockInterface|\Mockery\LegacyMockInterface
     */
    private $container;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpRedis();
    }

    protected function tearDown(): void
    {
        $this->tearDownRedis();

        parent::tearDown();
    }

    /**
     * @param  string  $driver
     * @param  string  $default
     * @param  string|null  $connection
     * @param  int  $retryAfter
     * @param  int|null  $blockFor
     */
    private function setQueue($driver, $default = 'default', $connection = null, $retryAfter = 60, $blockFor = null)
    {
        $this->queue = new RedisQueue($this->redis[$driver], $default, $connection, $retryAfter, $blockFor);
        $this->container = m::spy(Container::class);
        $this->queue->setContainer($this->container);
    }

    /**
     * @param  string  $driver
     */
    #[DataProvider('redisDriverProvider')]
    public function testExpiredJobsArePopped($driver)
    {
        $default = config('queue.connections.redis.queue', 'default');
        $this->setQueue($driver, $default);

        $jobs = [
            new RedisQueueIntegrationTestJob(0),
            new RedisQueueIntegrationTestJob(1),
            new RedisQueueIntegrationTestJob(2),
            new RedisQueueIntegrationTestJob(3),
        ];

        $this->queue->later(1000, $jobs[0]);
        $this->queue->later(-200, $jobs[1]);
        $this->queue->later(-300, $jobs[2]);
        $this->queue->later(-100, $jobs[3]);

        $this->assertEquals($jobs[2], unserialize(json_decode($this->queue->pop()->getRawBody())->data->command));
        $this->assertEquals($jobs[1], unserialize(json_decode($this->queue->pop()->getRawBody())->data->command));
        $this->assertEquals($jobs[3], unserialize(json_decode($this->queue->pop()->getRawBody())->data->command));
        $this->assertNull($this->queue->pop());

        $this->assertEquals(1, $this->redis[$driver]->connection()->zcard("queues:$default:delayed"));
        $this->assertEquals(3, $this->redis[$driver]->connection()->zcard("queues:$default:reserved"));
    }

    /**
     * @param  mixed  $driver
     *
     * @throws \Exception
     */
    #[DataProvider('redisDriverProvider')]
    #[RequiresPhpExtension('pcntl')]
    public function testBlockingPop($driver)
    {
        $this->tearDownRedis();

        $default = config('queue.connections.redis.queue', 'default');
        if ($pid = pcntl_fork() > 0) {
            $this->setUpRedis();
            $this->setQueue($driver, $default, null, 60, 10);
            $this->assertEquals(12, unserialize(json_decode($this->queue->pop()->getRawBody())->data->command)->i);
        } elseif ($pid == 0) {
            $this->setUpRedis();
            $this->setQueue('phpredis', $default);
            sleep(1);
            $this->queue->push(new RedisQueueIntegrationTestJob(12));
            exit;
        } else {
            $this->fail('Cannot fork');
        }
    }

    // /**
    //  * @param  string  $driver
    //  */
    // #[DataProvider('redisDriverProvider')]
    // public function testMigrateMoreThan100Jobs($driver)
    // {
    //     $default = config('queue.connections.redis.queue', 'default');
    //     $this->setQueue($driver, $default);
    //     for ($i = -1; $i >= -201; $i--) {
    //         $this->queue->later($i, new RedisQueueIntegrationTestJob($i));
    //     }
    //     for ($i = -201; $i <= -1; $i++) {
    //         $this->assertEquals($i, unserialize(json_decode($this->queue->pop()->getRawBody())->data->command)->i);
    //         $this->assertEquals(-$i - 1, $this->redis[$driver]->llen("queues:$default:notify"));
    //     }
    // }

    /**
     * @param  string  $driver
     */
    #[DataProvider('redisDriverProvider')]
    public function testPopProperlyPopsJobOffOfRedis($driver)
    {
        $default = config('queue.connections.redis.queue', 'default');
        $this->setQueue($driver, $default);

        // Push an item into queue
        $job = new RedisQueueIntegrationTestJob(10);
        $this->queue->push($job);

        // Pop and check it is popped correctly
        $before = $this->currentTime();
        /** @var \Kasi\Queue\Jobs\RedisJob $redisJob */
        $redisJob = $this->queue->pop();
        $after = $this->currentTime();

        $this->assertEquals($job, unserialize(json_decode($redisJob->getRawBody())->data->command));
        $this->assertEquals(1, $redisJob->attempts());
        $this->assertEquals($job, unserialize(json_decode($redisJob->getReservedJob())->data->command));
        $this->assertEquals(1, json_decode($redisJob->getReservedJob())->attempts);
        $this->assertEquals($redisJob->getJobId(), json_decode($redisJob->getReservedJob())->id);

        // Check reserved queue
        $this->assertEquals(1, $this->redis[$driver]->connection()->zcard("queues:$default:reserved"));
        $result = $this->redis[$driver]->connection()->zrangebyscore("queues:$default:reserved", -INF, INF, ['withscores' => true]);
        $reservedJob = array_keys($result)[0];
        $score = (int) $result[$reservedJob];
        $this->assertLessThanOrEqual($score, $before + 60);
        $this->assertGreaterThanOrEqual($score, $after + 60);
        $this->assertEquals($job, unserialize(json_decode($reservedJob)->data->command));
    }

    /**
     * @param  string  $driver
     */
    #[DataProvider('redisDriverProvider')]
    public function testPopProperlyPopsDelayedJobOffOfRedis($driver)
    {
        $default = config('queue.connections.redis.queue', 'default');
        $this->setQueue($driver, $default);
        // Push an item into queue
        $job = new RedisQueueIntegrationTestJob(10);
        $this->queue->later(-10, $job);

        // Pop and check it is popped correctly
        $before = $this->currentTime();
        $this->assertEquals($job, unserialize(json_decode($this->queue->pop()->getRawBody())->data->command));
        $after = $this->currentTime();

        // Check reserved queue
        $this->assertEquals(1, $this->redis[$driver]->connection()->zcard("queues:$default:reserved"));
        $result = $this->redis[$driver]->connection()->zrangebyscore("queues:$default:reserved", -INF, INF, ['withscores' => true]);
        $reservedJob = array_keys($result)[0];
        $score = (int) $result[$reservedJob];
        $this->assertLessThanOrEqual($score, $before + 60);
        $this->assertGreaterThanOrEqual($score, $after + 60);
        $this->assertEquals($job, unserialize(json_decode($reservedJob)->data->command));
    }

    /**
     * @param  string  $driver
     */
    #[DataProvider('redisDriverProvider')]
    public function testPopPopsDelayedJobOffOfRedisWhenExpireNull($driver)
    {
        $default = config('queue.connections.redis.queue', 'default');
        $this->setQueue($driver, $default, null, null);

        // Push an item into queue
        $job = new RedisQueueIntegrationTestJob(10);
        $this->queue->later(-10, $job);

        $this->container->shouldHaveReceived('bound')->with('events')->twice();

        // Pop and check it is popped correctly
        $before = $this->currentTime();
        $this->assertEquals($job, unserialize(json_decode($this->queue->pop()->getRawBody())->data->command));
        $after = $this->currentTime();

        // Check reserved queue
        $this->assertEquals(1, $this->redis[$driver]->connection()->zcard("queues:$default:reserved"));
        $result = $this->redis[$driver]->connection()->zrangebyscore("queues:$default:reserved", -INF, INF, ['withscores' => true]);
        $reservedJob = array_keys($result)[0];
        $score = (int) $result[$reservedJob];
        $this->assertLessThanOrEqual($score, $before);
        $this->assertGreaterThanOrEqual($score, $after);
        $this->assertEquals($job, unserialize(json_decode($reservedJob)->data->command));
    }

    /**
     * @param  string  $driver
     */
    #[DataProvider('redisDriverProvider')]
    public function testBlockingPopProperlyPopsJobOffOfRedis($driver)
    {
        $default = config('queue.connections.redis.queue', 'default');
        $this->setQueue($driver, $default, null, 60, 5);

        // Push an item into queue
        $job = new RedisQueueIntegrationTestJob(10);
        $this->queue->push($job);

        // Pop and check it is popped correctly
        /** @var \Kasi\Queue\Jobs\RedisJob $redisJob */
        $redisJob = $this->queue->pop();

        $this->assertNotNull($redisJob);
        $this->assertEquals($job, unserialize(json_decode($redisJob->getReservedJob())->data->command));
    }

    /**
     * @param  string  $driver
     */
    #[DataProvider('redisDriverProvider')]
    public function testBlockingPopProperlyPopsExpiredJobs($driver)
    {
        Str::createUuidsUsing(function () {
            return 'uuid';
        });

        $default = config('queue.connections.redis.queue', 'default');
        $this->setQueue($driver, $default, null, 60, 5);

        $jobs = [
            new RedisQueueIntegrationTestJob(0),
            new RedisQueueIntegrationTestJob(1),
        ];

        $this->queue->later(-200, $jobs[0]);
        $this->queue->later(-200, $jobs[1]);

        $this->assertEquals($jobs[0], unserialize(json_decode($this->queue->pop()->getRawBody())->data->command));
        $this->assertEquals($jobs[1], unserialize(json_decode($this->queue->pop()->getRawBody())->data->command));

        $this->assertEquals(0, $this->redis[$driver]->connection()->llen('queues:default:notify'));
        $this->assertEquals(0, $this->redis[$driver]->connection()->zcard("queues:$default:delayed"));
        $this->assertEquals(2, $this->redis[$driver]->connection()->zcard("queues:$default:reserved"));

        Str::createUuidsNormally();
    }

    /**
     * @param  string  $driver
     */
    #[DataProvider('redisDriverProvider')]
    public function testNotExpireJobsWhenExpireNull($driver)
    {
        $default = config('queue.connections.redis.queue', 'default');
        $this->setQueue($driver, $default, null, null);

        // Make an expired reserved job
        $failed = new RedisQueueIntegrationTestJob(-20);
        $this->queue->push($failed);
        $this->container->shouldHaveReceived('bound')->with('events')->twice();

        $beforeFailPop = $this->currentTime();
        $this->queue->pop();
        $afterFailPop = $this->currentTime();

        // Push an item into queue
        $job = new RedisQueueIntegrationTestJob(10);
        $this->queue->push($job);
        $this->container->shouldHaveReceived('bound')->with('events')->times(4);

        // Pop and check it is popped correctly
        $before = $this->currentTime();
        $this->assertEquals($job, unserialize(json_decode($this->queue->pop()->getRawBody())->data->command));
        $after = $this->currentTime();

        // Check reserved queue
        $this->assertEquals(2, $this->redis[$driver]->connection()->zcard("queues:$default:reserved"));
        $result = $this->redis[$driver]->connection()->zrangebyscore("queues:$default:reserved", -INF, INF, ['withscores' => true]);

        foreach ($result as $payload => $score) {
            $command = unserialize(json_decode($payload)->data->command);
            $this->assertInstanceOf(RedisQueueIntegrationTestJob::class, $command);
            $this->assertContains($command->i, [10, -20]);

            $score = (int) $score;

            if ($command->i == 10) {
                $this->assertLessThanOrEqual($score, $before);
                $this->assertGreaterThanOrEqual($score, $after);
            } else {
                $this->assertLessThanOrEqual($score, $beforeFailPop);
                $this->assertGreaterThanOrEqual($score, $afterFailPop);
            }
        }
    }

    /**
     * @param  string  $driver
     */
    #[DataProvider('redisDriverProvider')]
    public function testExpireJobsWhenExpireSet($driver)
    {
        $default = config('queue.connections.redis.queue', 'default');
        $this->setQueue($driver, $default, null, 30);

        // Push an item into queue
        $job = new RedisQueueIntegrationTestJob(10);
        $this->queue->push($job);
        $this->container->shouldHaveReceived('bound')->with('events')->twice();

        // Pop and check it is popped correctly
        $before = $this->currentTime();
        $this->assertEquals($job, unserialize(json_decode($this->queue->pop()->getRawBody())->data->command));
        $after = $this->currentTime();

        // Check reserved queue
        $this->assertEquals(1, $this->redis[$driver]->connection()->zcard("queues:$default:reserved"));
        $result = $this->redis[$driver]->connection()->zrangebyscore("queues:$default:reserved", -INF, INF, ['withscores' => true]);
        $reservedJob = array_keys($result)[0];
        $score = (int) $result[$reservedJob];
        $this->assertLessThanOrEqual($score, $before + 30);
        $this->assertGreaterThanOrEqual($score, $after + 30);
        $this->assertEquals($job, unserialize(json_decode($reservedJob)->data->command));
    }

    /**
     * @param  string  $driver
     */
    #[DataProvider('redisDriverProvider')]
    public function testRelease($driver)
    {
        $default = config('queue.connections.redis.queue', 'default');
        $this->setQueue($driver, $default);

        // push a job into queue
        $job = new RedisQueueIntegrationTestJob(30);
        $this->queue->push($job);

        // pop and release the job
        /** @var \Kasi\Queue\Jobs\RedisJob $redisJob */
        $redisJob = $this->queue->pop();
        $before = $this->currentTime();
        $redisJob->release(1000);
        $after = $this->currentTime();

        // check the content of delayed queue
        $this->assertEquals(1, $this->redis[$driver]->connection()->zcard("queues:$default:delayed"));

        $results = $this->redis[$driver]->connection()->zrangebyscore("queues:$default:delayed", -INF, INF, ['withscores' => true]);

        $payload = array_keys($results)[0];

        $score = $results[$payload];

        $this->assertGreaterThanOrEqual($before + 1000, $score);
        $this->assertLessThanOrEqual($after + 1000, $score);

        $decoded = json_decode($payload);

        $this->assertEquals(1, $decoded->attempts);
        $this->assertEquals($job, unserialize($decoded->data->command));

        // check if the queue has no ready item yet
        $this->assertNull($this->queue->pop());
    }

    /**
     * @param  string  $driver
     */
    #[DataProvider('redisDriverProvider')]
    public function testReleaseInThePast($driver)
    {
        $default = config('queue.connections.redis.queue', 'default');
        $this->setQueue($driver, $default);
        $job = new RedisQueueIntegrationTestJob(30);
        $this->queue->push($job);

        /** @var \Kasi\Queue\Jobs\RedisJob $redisJob */
        $redisJob = $this->queue->pop();
        $redisJob->release(-3);

        $this->assertInstanceOf(RedisJob::class, $this->queue->pop());
    }

    /**
     * @param  string  $driver
     */
    #[DataProvider('redisDriverProvider')]
    public function testDelete($driver)
    {
        $default = config('queue.connections.redis.queue', 'default');
        $this->setQueue($driver, $default);

        $job = new RedisQueueIntegrationTestJob(30);
        $this->queue->push($job);

        /** @var \Kasi\Queue\Jobs\RedisJob $redisJob */
        $redisJob = $this->queue->pop();

        $redisJob->delete();

        $this->assertEquals(0, $this->redis[$driver]->connection()->zcard("queues:$default:delayed"));
        $this->assertEquals(0, $this->redis[$driver]->connection()->zcard("queues:$default:reserved"));
        $this->assertEquals(0, $this->redis[$driver]->connection()->llen("queues:$default"));

        $this->assertNull($this->queue->pop());
    }

    /**
     * @param  string  $driver
     */
    #[DataProvider('redisDriverProvider')]
    public function testClear($driver)
    {
        $default = config('queue.connections.redis.queue', 'default');
        $this->setQueue($driver, $default);

        $job1 = new RedisQueueIntegrationTestJob(30);
        $job2 = new RedisQueueIntegrationTestJob(40);

        $this->queue->push($job1);
        $this->queue->push($job2);

        $this->assertEquals(2, $this->queue->clear(null));
        $this->assertEquals(0, $this->queue->size());
        $this->assertEquals(0, $this->redis[$driver]->connection()->llen('queues:default:notify'));
    }

    /**
     * @param  string  $driver
     */
    #[DataProvider('redisDriverProvider')]
    public function testSize($driver)
    {
        $default = config('queue.connections.redis.queue', 'default');
        $this->setQueue($driver, $default);
        $this->assertEquals(0, $this->queue->size());
        $this->queue->push(new RedisQueueIntegrationTestJob(1));
        $this->assertEquals(1, $this->queue->size());
        $this->queue->later(60, new RedisQueueIntegrationTestJob(2));
        $this->assertEquals(2, $this->queue->size());
        $this->queue->push(new RedisQueueIntegrationTestJob(3));
        $this->assertEquals(3, $this->queue->size());
        $job = $this->queue->pop();
        $this->assertEquals(3, $this->queue->size());
        $job->delete();
        $this->assertEquals(2, $this->queue->size());
    }

    /**
     * @param  string  $driver
     */
    #[DataProvider('redisDriverProvider')]
    public function testPushJobQueueingAndJobQueuedEvents($driver)
    {
        $events = m::mock(Dispatcher::class);
        $events->shouldReceive('dispatch')->withArgs(function (JobQueueing $jobQueuing) {
            $this->assertInstanceOf(RedisQueueIntegrationTestJob::class, $jobQueuing->job);

            return true;
        })->andReturnNull()->once();
        $events->shouldReceive('dispatch')->withArgs(function (JobQueued $jobQueued) {
            $this->assertInstanceOf(RedisQueueIntegrationTestJob::class, $jobQueued->job);
            $this->assertIsString($jobQueued->id);

            return true;
        })->andReturnNull()->once();

        $container = m::mock(Container::class);
        $container->shouldReceive('bound')->with('events')->andReturn(true)->twice();
        $container->shouldReceive('offsetGet')->with('events')->andReturn($events)->twice();

        $default = config('queue.connections.redis.queue', 'default');
        $queue = new RedisQueue($this->redis[$driver], $default);
        $queue->setContainer($container);

        $queue->push(new RedisQueueIntegrationTestJob(5));
    }

    /**
     * @param  string  $driver
     */
    #[DataProvider('redisDriverProvider')]
    public function testBulkJobQueuedEvent($driver)
    {
        $events = m::mock(Dispatcher::class);
        $events->shouldReceive('dispatch')->with(m::type(JobQueueing::class))->andReturnNull()->times(3);
        $events->shouldReceive('dispatch')->with(m::type(JobQueued::class))->andReturnNull()->times(3);

        $container = m::mock(Container::class);
        $container->shouldReceive('bound')->with('events')->andReturn(true)->times(6);
        $container->shouldReceive('offsetGet')->with('events')->andReturn($events)->times(6);

        $default = config('queue.connections.redis.queue', 'default');
        $queue = new RedisQueue($this->redis[$driver], $default);
        $queue->setContainer($container);

        $queue->bulk([
            new RedisQueueIntegrationTestJob(5),
            new RedisQueueIntegrationTestJob(10),
            new RedisQueueIntegrationTestJob(15),
        ]);
    }
}

class RedisQueueIntegrationTestJob
{
    public $i;

    public function __construct($i)
    {
        $this->i = $i;
    }

    public function handle()
    {
        //
    }
}
