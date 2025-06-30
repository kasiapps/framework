<?php

namespace Database;

use Generator;
use Kasi\Database\MariaDbConnection;
use Kasi\Database\Schema\MariaDbSchemaState;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class DatabaseMariaDbSchemaStateTest extends TestCase
{
    #[DataProvider('provider')]
    public function testConnectionString(string $expectedConnectionString, array $expectedVariables, array $dbConfig): void
    {
        $connection = $this->createMock(MariaDbConnection::class);
        $connection->method('getConfig')->willReturn($dbConfig);

        $schemaState = new MariaDbSchemaState($connection);

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
}
