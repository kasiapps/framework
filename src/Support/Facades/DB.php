<?php

namespace Kasi\Support\Facades;

use Kasi\Database\Console\Migrations\FreshCommand;
use Kasi\Database\Console\Migrations\RefreshCommand;
use Kasi\Database\Console\Migrations\ResetCommand;
use Kasi\Database\Console\Migrations\RollbackCommand;
use Kasi\Database\Console\WipeCommand;

/**
 * @method static \Kasi\Database\Connection connection(string|null $name = null)
 * @method static \Kasi\Database\ConnectionInterface build(array $config)
 * @method static string calculateDynamicConnectionName(array $config)
 * @method static \Kasi\Database\ConnectionInterface connectUsing(string $name, array $config, bool $force = false)
 * @method static void purge(string|null $name = null)
 * @method static void disconnect(string|null $name = null)
 * @method static \Kasi\Database\Connection reconnect(string|null $name = null)
 * @method static mixed usingConnection(string $name, callable $callback)
 * @method static string getDefaultConnection()
 * @method static void setDefaultConnection(string $name)
 * @method static string[] supportedDrivers()
 * @method static string[] availableDrivers()
 * @method static void extend(string $name, callable $resolver)
 * @method static void forgetExtension(string $name)
 * @method static array getConnections()
 * @method static void setReconnector(callable $reconnector)
 * @method static \Kasi\Database\DatabaseManager setApplication(\Kasi\Contracts\Foundation\Application $app)
 * @method static void macro(string $name, object|callable $macro)
 * @method static void mixin(object $mixin, bool $replace = true)
 * @method static bool hasMacro(string $name)
 * @method static void flushMacros()
 * @method static mixed macroCall(string $method, array $parameters)
 * @method static void useDefaultQueryGrammar()
 * @method static void useDefaultSchemaGrammar()
 * @method static void useDefaultPostProcessor()
 * @method static \Kasi\Database\Schema\Builder getSchemaBuilder()
 * @method static \Kasi\Database\Query\Builder table(\Closure|\Kasi\Database\Query\Builder|\Kasi\Contracts\Database\Query\Expression|string $table, string|null $as = null)
 * @method static \Kasi\Database\Query\Builder query()
 * @method static mixed selectOne(string $query, array $bindings = [], bool $useReadPdo = true)
 * @method static mixed scalar(string $query, array $bindings = [], bool $useReadPdo = true)
 * @method static array selectFromWriteConnection(string $query, array $bindings = [])
 * @method static array select(string $query, array $bindings = [], bool $useReadPdo = true)
 * @method static array selectResultSets(string $query, array $bindings = [], bool $useReadPdo = true)
 * @method static \Generator cursor(string $query, array $bindings = [], bool $useReadPdo = true)
 * @method static bool insert(string $query, array $bindings = [])
 * @method static int update(string $query, array $bindings = [])
 * @method static int delete(string $query, array $bindings = [])
 * @method static bool statement(string $query, array $bindings = [])
 * @method static int affectingStatement(string $query, array $bindings = [])
 * @method static bool unprepared(string $query)
 * @method static int|null threadCount()
 * @method static array pretend(\Closure $callback)
 * @method static mixed withoutPretending(\Closure $callback)
 * @method static void bindValues(\PDOStatement $statement, array $bindings)
 * @method static array prepareBindings(array $bindings)
 * @method static void logQuery(string $query, array $bindings, float|null $time = null)
 * @method static void whenQueryingForLongerThan(\DateTimeInterface|\Carbon\CarbonInterval|float|int $threshold, callable $handler)
 * @method static void allowQueryDurationHandlersToRunAgain()
 * @method static float totalQueryDuration()
 * @method static void resetTotalQueryDuration()
 * @method static void reconnectIfMissingConnection()
 * @method static \Kasi\Database\Connection beforeStartingTransaction(\Closure $callback)
 * @method static \Kasi\Database\Connection beforeExecuting(\Closure $callback)
 * @method static void listen(\Closure $callback)
 * @method static \Kasi\Contracts\Database\Query\Expression raw(mixed $value)
 * @method static string escape(string|float|int|bool|null $value, bool $binary = false)
 * @method static bool hasModifiedRecords()
 * @method static void recordsHaveBeenModified(bool $value = true)
 * @method static \Kasi\Database\Connection setRecordModificationState(bool $value)
 * @method static void forgetRecordModificationState()
 * @method static \Kasi\Database\Connection useWriteConnectionWhenReading(bool $value = true)
 * @method static \PDO getPdo()
 * @method static \PDO|\Closure|null getRawPdo()
 * @method static \PDO getReadPdo()
 * @method static \PDO|\Closure|null getRawReadPdo()
 * @method static \Kasi\Database\Connection setPdo(\PDO|\Closure|null $pdo)
 * @method static \Kasi\Database\Connection setReadPdo(\PDO|\Closure|null $pdo)
 * @method static string|null getName()
 * @method static string|null getNameWithReadWriteType()
 * @method static mixed getConfig(string|null $option = null)
 * @method static string getDriverName()
 * @method static string getDriverTitle()
 * @method static \Kasi\Database\Query\Grammars\Grammar getQueryGrammar()
 * @method static \Kasi\Database\Connection setQueryGrammar(\Kasi\Database\Query\Grammars\Grammar $grammar)
 * @method static \Kasi\Database\Schema\Grammars\Grammar getSchemaGrammar()
 * @method static \Kasi\Database\Connection setSchemaGrammar(\Kasi\Database\Schema\Grammars\Grammar $grammar)
 * @method static \Kasi\Database\Query\Processors\Processor getPostProcessor()
 * @method static \Kasi\Database\Connection setPostProcessor(\Kasi\Database\Query\Processors\Processor $processor)
 * @method static \Kasi\Contracts\Events\Dispatcher getEventDispatcher()
 * @method static \Kasi\Database\Connection setEventDispatcher(\Kasi\Contracts\Events\Dispatcher $events)
 * @method static void unsetEventDispatcher()
 * @method static \Kasi\Database\Connection setTransactionManager(\Kasi\Database\DatabaseTransactionsManager $manager)
 * @method static void unsetTransactionManager()
 * @method static bool pretending()
 * @method static array getQueryLog()
 * @method static array getRawQueryLog()
 * @method static void flushQueryLog()
 * @method static void enableQueryLog()
 * @method static void disableQueryLog()
 * @method static bool logging()
 * @method static string getDatabaseName()
 * @method static \Kasi\Database\Connection setDatabaseName(string $database)
 * @method static \Kasi\Database\Connection setReadWriteType(string|null $readWriteType)
 * @method static string getTablePrefix()
 * @method static \Kasi\Database\Connection setTablePrefix(string $prefix)
 * @method static \Kasi\Database\Grammar withTablePrefix(\Kasi\Database\Grammar $grammar)
 * @method static void withoutTablePrefix(\Closure $callback)
 * @method static string getServerVersion()
 * @method static void resolverFor(string $driver, \Closure $callback)
 * @method static \Closure|null getResolver(string $driver)
 * @method static mixed transaction(\Closure $callback, int $attempts = 1)
 * @method static void beginTransaction()
 * @method static void commit()
 * @method static void rollBack(int|null $toLevel = null)
 * @method static int transactionLevel()
 * @method static void afterCommit(callable $callback)
 *
 * @see \Kasi\Database\DatabaseManager
 */
class DB extends Facade
{
    /**
     * Indicate if destructive Artisan commands should be prohibited.
     *
     * Prohibits: db:wipe, migrate:fresh, migrate:refresh, and migrate:reset
     *
     * @param  bool  $prohibit
     * @return void
     */
    public static function prohibitDestructiveCommands(bool $prohibit = true)
    {
        FreshCommand::prohibit($prohibit);
        RefreshCommand::prohibit($prohibit);
        ResetCommand::prohibit($prohibit);
        RollbackCommand::prohibit($prohibit);
        WipeCommand::prohibit($prohibit);
    }

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'db';
    }
}
