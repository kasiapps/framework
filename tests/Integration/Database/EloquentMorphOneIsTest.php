<?php

namespace Kasi\Tests\Integration\Database\EloquentMorphOneIsTest;

use Kasi\Database\Eloquent\Model;
use Kasi\Database\Schema\Blueprint;
use Kasi\Support\Facades\Schema;
use Kasi\Tests\Integration\Database\DatabaseTestCase;

class EloquentMorphOneIsTest extends DatabaseTestCase
{
    protected function afterRefreshingDatabase()
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
        });

        Schema::create('attachments', function (Blueprint $table) {
            $table->increments('id');
            $table->string('attachable_type')->nullable();
            $table->integer('attachable_id')->nullable();
        });

        $post = Post::create();
        $post->attachment()->create();
    }

    public function testChildIsNotNull()
    {
        $parent = Post::first();
        $child = null;

        $this->assertFalse($parent->attachment()->is($child));
        $this->assertTrue($parent->attachment()->isNot($child));
    }

    public function testChildIsModel()
    {
        $parent = Post::first();
        $child = Attachment::first();

        $this->assertTrue($parent->attachment()->is($child));
        $this->assertFalse($parent->attachment()->isNot($child));
    }

    public function testChildIsNotAnotherModel()
    {
        $parent = Post::first();
        $child = new Attachment;
        $child->id = 2;

        $this->assertFalse($parent->attachment()->is($child));
        $this->assertTrue($parent->attachment()->isNot($child));
    }

    public function testNullChildIsNotModel()
    {
        $parent = Post::first();
        $child = Attachment::first();
        $child->attachable_type = null;
        $child->attachable_id = null;

        $this->assertFalse($parent->attachment()->is($child));
        $this->assertTrue($parent->attachment()->isNot($child));
    }

    public function testChildIsNotModelWithAnotherTable()
    {
        $parent = Post::first();
        $child = Attachment::first();
        $child->setTable('foo');

        $this->assertFalse($parent->attachment()->is($child));
        $this->assertTrue($parent->attachment()->isNot($child));
    }

    public function testChildIsNotModelWithAnotherConnection()
    {
        $parent = Post::first();
        $child = Attachment::first();
        $child->setConnection('foo');

        $this->assertFalse($parent->attachment()->is($child));
        $this->assertTrue($parent->attachment()->isNot($child));
    }
}

class Attachment extends Model
{
    public $timestamps = false;
}

class Post extends Model
{
    public function attachment()
    {
        return $this->morphOne(Attachment::class, 'attachable');
    }
}
