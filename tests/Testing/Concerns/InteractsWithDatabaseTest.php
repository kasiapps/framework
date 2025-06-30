<?php

namespace Kasi\Tests\Testing\Concerns;

use Kasi\Database\ConnectionInterface;
use Kasi\Database\Query\Expression;
use Kasi\Database\Query\Grammars\MariaDbGrammar;
use Kasi\Database\Query\Grammars\MySqlGrammar;
use Kasi\Database\Query\Grammars\PostgresGrammar;
use Kasi\Database\Query\Grammars\SQLiteGrammar;
use Kasi\Database\Query\Grammars\SqlServerGrammar;
use Kasi\Foundation\Testing\Concerns\InteractsWithDatabase;
use Kasi\Support\Facades\DB;
use Kasi\Support\Facades\Facade;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class InteractsWithDatabaseTest extends TestCase
{
    protected function setUp(): void
    {
        Facade::clearResolvedInstances();
        Facade::setFacadeApplication(null);
    }

    protected function tearDown(): void
    {
        m::close();
    }

    public function testCastToJsonSqlite()
    {
        $grammar = new SQLiteGrammar();

        $this->assertEquals(<<<'TEXT'
        '["foo","bar"]'
        TEXT,
            $this->castAsJson(['foo', 'bar'], $grammar)
        );

        $this->assertEquals(<<<'TEXT'
        '["foo","bar"]'
        TEXT,
            $this->castAsJson(collect(['foo', 'bar']), $grammar)
        );

        $this->assertEquals(<<<'TEXT'
        '{"foo":"bar"}'
        TEXT,
            $this->castAsJson((object) ['foo' => 'bar'], $grammar)
        );
    }

    public function testCastToJsonPostgres()
    {
        $grammar = new PostgresGrammar();

        $this->assertEquals(<<<'TEXT'
        '["foo","bar"]'
        TEXT,
            $this->castAsJson(['foo', 'bar'], $grammar)
        );

        $this->assertEquals(<<<'TEXT'
        '["foo","bar"]'
        TEXT,
            $this->castAsJson(collect(['foo', 'bar']), $grammar)
        );

        $this->assertEquals(<<<'TEXT'
        '{"foo":"bar"}'
        TEXT,
            $this->castAsJson((object) ['foo' => 'bar'], $grammar)
        );
    }

    public function testCastToJsonSqlServer()
    {
        $grammar = new SqlServerGrammar();

        $this->assertEquals(<<<'TEXT'
        json_query('["foo","bar"]')
        TEXT,
            $this->castAsJson(['foo', 'bar'], $grammar)
        );

        $this->assertEquals(<<<'TEXT'
        json_query('["foo","bar"]')
        TEXT,
            $this->castAsJson(collect(['foo', 'bar']), $grammar)
        );

        $this->assertEquals(<<<'TEXT'
        json_query('{"foo":"bar"}')
        TEXT,
            $this->castAsJson((object) ['foo' => 'bar'], $grammar)
        );
    }

    public function testCastToJsonMySql()
    {
        $grammar = new MySqlGrammar();

        $this->assertEquals(<<<'TEXT'
        cast('["foo","bar"]' as json)
        TEXT,
            $this->castAsJson(['foo', 'bar'], $grammar)
        );

        $this->assertEquals(<<<'TEXT'
        cast('["foo","bar"]' as json)
        TEXT,
            $this->castAsJson(collect(['foo', 'bar']), $grammar)
        );

        $this->assertEquals(<<<'TEXT'
        cast('{"foo":"bar"}' as json)
        TEXT,
            $this->castAsJson((object) ['foo' => 'bar'], $grammar)
        );
    }

    public function testCastToJsonMariaDb()
    {
        $grammar = new MariaDbGrammar();

        $this->assertEquals(<<<'TEXT'
        json_query('["foo","bar"]', '$')
        TEXT,
            $this->castAsJson(['foo', 'bar'], $grammar)
        );

        $this->assertEquals(<<<'TEXT'
        json_query('["foo","bar"]', '$')
        TEXT,
            $this->castAsJson(collect(['foo', 'bar']), $grammar)
        );

        $this->assertEquals(<<<'TEXT'
        json_query('{"foo":"bar"}', '$')
        TEXT,
            $this->castAsJson((object) ['foo' => 'bar'], $grammar)
        );
    }

    protected function castAsJson($value, $grammar)
    {
        $connection = m::mock(ConnectionInterface::class);

        $connection->shouldReceive('getQueryGrammar')->andReturn($grammar);

        $connection->shouldReceive('raw')->andReturnUsing(function ($value) {
            return new Expression($value);
        });

        $connection->shouldReceive('getPdo->quote')->andReturnUsing(function ($value) {
            return "'".$value."'";
        });

        DB::shouldReceive('connection')->with(null)->andReturn($connection);

        $instance = new class
        {
            use InteractsWithDatabase;
        };

        return $instance->castAsJson($value)->getValue($grammar);
    }
}
