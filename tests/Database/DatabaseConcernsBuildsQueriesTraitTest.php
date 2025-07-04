<?php

namespace Kasi\Tests\Database;

use Kasi\Database\Concerns\BuildsQueries;
use PHPUnit\Framework\TestCase;

class DatabaseConcernsBuildsQueriesTraitTest extends TestCase
{
    public function testTapCallbackInstance()
    {
        $mock = new class
        {
            use BuildsQueries;
        };

        $mock->tap(function ($builder) use ($mock) {
            $this->assertEquals($mock, $builder);
        });
    }
}
