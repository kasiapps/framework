<?php

namespace Database;

use Kasi\Database\Connection;
use Kasi\Database\Query\Processors\MariaDbProcessor;
use Kasi\Database\Schema\Grammars\MariaDbGrammar;
use Kasi\Database\Schema\MariaDbBuilder;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class DatabaseMariaDbSchemaBuilderTest extends TestCase
{
    protected function tearDown(): void
    {
        m::close();
    }

    public function testHasTable()
    {
        $connection = m::mock(Connection::class);
        $grammar = m::mock(MariaDbGrammar::class);
        $connection->shouldReceive('getDatabaseName')->andReturn('db');
        $connection->shouldReceive('getSchemaGrammar')->andReturn($grammar);
        $builder = new MariaDbBuilder($connection);
        $grammar->shouldReceive('compileTableExists')->once()->andReturn('sql');
        $connection->shouldReceive('getTablePrefix')->once()->andReturn('prefix_');
        $connection->shouldReceive('scalar')->once()->with('sql')->andReturn(1);

        $this->assertTrue($builder->hasTable('table'));
    }

    public function testGetColumnListing()
    {
        $connection = m::mock(Connection::class);
        $grammar = m::mock(MariaDbGrammar::class);
        $processor = m::mock(MariaDbProcessor::class);
        $connection->shouldReceive('getDatabaseName')->andReturn('db');
        $connection->shouldReceive('getSchemaGrammar')->andReturn($grammar);
        $connection->shouldReceive('getPostProcessor')->andReturn($processor);
        $grammar->shouldReceive('compileColumns')->with('db', 'prefix_table')->once()->andReturn('sql');
        $processor->shouldReceive('processColumns')->once()->andReturn([['name' => 'column']]);
        $builder = new MariaDbBuilder($connection);
        $connection->shouldReceive('getTablePrefix')->once()->andReturn('prefix_');
        $connection->shouldReceive('selectFromWriteConnection')->once()->with('sql')->andReturn([['name' => 'column']]);

        $this->assertEquals(['column'], $builder->getColumnListing('table'));
    }
}
