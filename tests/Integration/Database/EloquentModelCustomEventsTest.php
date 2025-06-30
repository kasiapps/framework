<?php

namespace Kasi\Tests\Integration\Database\EloquentModelCustomEventsTest;

use Kasi\Database\Eloquent\Model;
use Kasi\Database\Schema\Blueprint;
use Kasi\Support\Facades\Event;
use Kasi\Support\Facades\Schema;
use Kasi\Tests\Integration\Database\DatabaseTestCase;

class EloquentModelCustomEventsTest extends DatabaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Event::listen(CustomEvent::class, function () {
            $_SERVER['fired_event'] = true;
        });
    }

    protected function afterRefreshingDatabase()
    {
        Schema::create('test_model1', function (Blueprint $table) {
            $table->increments('id');
        });
    }

    public function testFlushListenersClearsCustomEvents()
    {
        $_SERVER['fired_event'] = false;

        TestModel1::flushEventListeners();

        TestModel1::create();

        $this->assertFalse($_SERVER['fired_event']);
    }

    public function testCustomEventListenersAreFired()
    {
        $_SERVER['fired_event'] = false;

        TestModel1::create();

        $this->assertTrue($_SERVER['fired_event']);
    }
}

class TestModel1 extends Model
{
    public $dispatchesEvents = ['created' => CustomEvent::class];
    public $table = 'test_model1';
    public $timestamps = false;
    protected $guarded = [];
}

class CustomEvent
{
    //
}
