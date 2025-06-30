<?php

namespace Kasi\Tests\Database;

use Exception;
use Generator;
use Kasi\Database\MySqlConnection;
use Kasi\Database\Schema\MySqlSchemaState;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use Symfony\Component\Process\Process;

class DatabaseMySqlSchemaStateTest extends TestCase
{
    #[DataProvider('provider')]
    public function testConnectionString(string $expectedConnectionString, array $expectedVariables, array $dbConfig): void
    {
        $connection = $this->createMock(MySqlConnection::class);
        $connection->method('getConfig')->willReturn($dbConfig);

        $schemaState = new MySqlSchemaState($connection);

        // test connectionString
        $method = new ReflectionMethod(get_class($schemaState), 'connectionString');
        $connString = $method->invoke($schemaState);

        self::assertEquals($expectedConnectionString, $connString);

        // test baseVariables
        $method = new ReflectionMethod(get_class($schemaState), 'baseVariables');
        $variables = $method->invoke($schemaState, $dbConfig);

        self::assertEquals($expectedVariables, $variables);
    }

    public static function provider(): Generator
    {
        yield 'default' => [
            ' --user="${:KASI_LOAD_USER}" --password="${:KASI_LOAD_PASSWORD}" --host="${:KASI_LOAD_HOST}" --port="${:KASI_LOAD_PORT}"', [
                'KASI_LOAD_SOCKET' => '',
                'KASI_LOAD_HOST' => '127.0.0.1',
                'KASI_LOAD_PORT' => '',
                'KASI_LOAD_USER' => 'root',
                'KASI_LOAD_PASSWORD' => '',
                'KASI_LOAD_DATABASE' => 'forge',
                'KASI_LOAD_SSL_CA' => '',
            ], [
                'username' => 'root',
                'host' => '127.0.0.1',
                'database' => 'forge',
            ],
        ];

        yield 'ssl_ca' => [
            ' --user="${:KASI_LOAD_USER}" --password="${:KASI_LOAD_PASSWORD}" --host="${:KASI_LOAD_HOST}" --port="${:KASI_LOAD_PORT}" --ssl-ca="${:KASI_LOAD_SSL_CA}"', [
                'KASI_LOAD_SOCKET' => '',
                'KASI_LOAD_HOST' => '',
                'KASI_LOAD_PORT' => '',
                'KASI_LOAD_USER' => 'root',
                'KASI_LOAD_PASSWORD' => '',
                'KASI_LOAD_DATABASE' => 'forge',
                'KASI_LOAD_SSL_CA' => 'ssl.ca',
            ], [
                'username' => 'root',
                'database' => 'forge',
                'options' => [
                    \PDO::MYSQL_ATTR_SSL_CA => 'ssl.ca',
                ],
            ],
        ];

        yield 'unix socket' => [
            ' --user="${:KASI_LOAD_USER}" --password="${:KASI_LOAD_PASSWORD}" --socket="${:KASI_LOAD_SOCKET}"', [
                'KASI_LOAD_SOCKET' => '/tmp/mysql.sock',
                'KASI_LOAD_HOST' => '',
                'KASI_LOAD_PORT' => '',
                'KASI_LOAD_USER' => 'root',
                'KASI_LOAD_PASSWORD' => '',
                'KASI_LOAD_DATABASE' => 'forge',
                'KASI_LOAD_SSL_CA' => '',
            ], [
                'username' => 'root',
                'database' => 'forge',
                'unix_socket' => '/tmp/mysql.sock',
            ],
        ];
    }

    public function testExecuteDumpProcessForDepth()
    {
        $mockProcess = $this->createMock(Process::class);
        $mockProcess->method('setTimeout')->willReturnSelf();
        $mockProcess->method('mustRun')->will(
            $this->throwException(new Exception('column-statistics'))
        );

        $mockOutput = $this->createMock(\stdClass::class);
        $mockVariables = [];

        $schemaState = $this->getMockBuilder(MySqlSchemaState::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['makeProcess'])
            ->getMock();

        $schemaState->method('makeProcess')->willReturn($mockProcess);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Dump execution exceeded maximum depth of 30.');

        // test executeDumpProcess
        $method = new ReflectionMethod(get_class($schemaState), 'executeDumpProcess');
        $method->invoke($schemaState, $mockProcess, $mockOutput, $mockVariables, 31);
    }
}
