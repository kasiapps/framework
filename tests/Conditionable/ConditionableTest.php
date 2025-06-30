<?php

namespace Kasi\Tests\Conditionable;

use Kasi\Database\Capsule\Manager as DB;
use Kasi\Database\Eloquent\Builder;
use Kasi\Database\Eloquent\Model;
use Kasi\Support\HigherOrderWhenProxy;
use PHPUnit\Framework\TestCase;

class ConditionableTest extends TestCase
{
    protected function setUp(): void
    {
        $db = new DB;

        $db->addConnection([
            'driver' => 'sqlite',
            'database' => ':memory:',
        ]);

        $db->bootEloquent();
        $db->setAsGlobal();
    }

    public function testWhen(): void
    {
        $this->assertInstanceOf(HigherOrderWhenProxy::class, TestConditionableModel::query()->when(true));
        $this->assertInstanceOf(HigherOrderWhenProxy::class, TestConditionableModel::query()->when(false));
        $this->assertInstanceOf(HigherOrderWhenProxy::class, TestConditionableModel::query()->when());
        $this->assertInstanceOf(Builder::class, TestConditionableModel::query()->when(false, null));
        $this->assertInstanceOf(Builder::class, TestConditionableModel::query()->when(true, function () {
        }));
    }

    public function testUnless(): void
    {
        $this->assertInstanceOf(HigherOrderWhenProxy::class, TestConditionableModel::query()->unless(true));
        $this->assertInstanceOf(HigherOrderWhenProxy::class, TestConditionableModel::query()->unless(false));
        $this->assertInstanceOf(HigherOrderWhenProxy::class, TestConditionableModel::query()->unless());
        $this->assertInstanceOf(Builder::class, TestConditionableModel::query()->unless(true, null));
        $this->assertInstanceOf(Builder::class, TestConditionableModel::query()->unless(false, function () {
        }));
    }
}

class TestConditionableModel extends Model
{
}
