<?php

namespace Kasi\Tests\Database;

use Kasi\Database\Query\Builder;
use Kasi\Database\Query\Expression;
use Kasi\Database\Query\Grammars\Grammar;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class DatabaseQueryGrammarTest extends TestCase
{
    protected function tearDown(): void
    {
        m::close();
    }

    public function testWhereRawReturnsStringWhenExpressionPassed()
    {
        $builder = m::mock(Builder::class);
        $grammar = new Grammar;
        $reflection = new ReflectionClass($grammar);
        $method = $reflection->getMethod('whereRaw');
        $expressionArray = ['sql' => new Expression('select * from "users"')];

        $rawQuery = $method->invoke($grammar, $builder, $expressionArray);

        $this->assertSame('select * from "users"', $rawQuery);
    }

    public function testWhereRawReturnsStringWhenStringPassed()
    {
        $builder = m::mock(Builder::class);
        $grammar = new Grammar;
        $reflection = new ReflectionClass($grammar);
        $method = $reflection->getMethod('whereRaw');
        $stringArray = ['sql' => 'select * from "users"'];

        $rawQuery = $method->invoke($grammar, $builder, $stringArray);

        $this->assertSame('select * from "users"', $rawQuery);
    }
}
