<?php

namespace Kasi\Tests\Integration\Database;

use Kasi\Database\Eloquent\Model;
use Kasi\Database\Schema\Blueprint;
use Kasi\Support\Facades\Schema;

class EloquentModelWithoutEventsTest extends DatabaseTestCase
{
    protected function afterRefreshingDatabase()
    {
        Schema::create('auto_filled_models', function (Blueprint $table) {
            $table->increments('id');
            $table->text('project')->nullable();
        });
    }

    public function testWithoutEventsRegistersBootedListenersForLater()
    {
        $model = AutoFilledModel::withoutEvents(function () {
            return AutoFilledModel::create();
        });

        $this->assertNull($model->project);

        $model->save();

        $this->assertSame('Kasi', $model->project);
    }
}

class AutoFilledModel extends Model
{
    public $table = 'auto_filled_models';
    public $timestamps = false;
    protected $guarded = [];

    public static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            $model->project = 'Kasi';
        });
    }
}
