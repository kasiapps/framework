<?php

namespace Kasi\Tests\Integration\Events;

use Kasi\Database\DatabaseTransactionsManager;
use Kasi\Support\Facades\Event;
use Mockery as m;
use Orchestra\Testbench\TestCase;

class ListenerTest extends TestCase
{
    protected function tearDown(): void
    {
        parent::tearDown();

        ListenerTestListener::$ran = false;
        ListenerTestListenerAfterCommit::$ran = false;

        parent::tearDown();
    }

    public function testClassListenerRunsNormallyIfNoTransactions()
    {
        $this->app->singleton('db.transactions', function () {
            $transactionManager = m::mock(DatabaseTransactionsManager::class);
            $transactionManager->shouldNotReceive('addCallback')->once()->andReturn(null);

            return $transactionManager;
        });

        Event::listen(ListenerTestEvent::class, ListenerTestListener::class);

        Event::dispatch(new ListenerTestEvent);

        $this->assertTrue(ListenerTestListener::$ran);
    }

    public function testClassListenerDoesntRunInsideTransaction()
    {
        $this->app->singleton('db.transactions', function () {
            $transactionManager = m::mock(DatabaseTransactionsManager::class);
            $transactionManager->shouldReceive('addCallback')->once()->andReturn(null);

            return $transactionManager;
        });

        Event::listen(ListenerTestEvent::class, ListenerTestListenerAfterCommit::class);

        Event::dispatch(new ListenerTestEvent);

        $this->assertFalse(ListenerTestListenerAfterCommit::$ran);
    }
}

class ListenerTestEvent
{
    //
}

class ListenerTestListener
{
    public static $ran = false;

    public function handle()
    {
        static::$ran = true;
    }
}

class ListenerTestListenerAfterCommit
{
    public static $ran = false;

    public $afterCommit = true;

    public function handle()
    {
        static::$ran = true;
    }
}
