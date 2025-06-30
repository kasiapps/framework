<?php

namespace Kasi\Database\Schema;

use Kasi\Database\Connection;
use Kasi\Support\Collection;

class PostgresSchemaState extends SchemaState
{
    /**
     * Dump the database's schema into a file.
     *
     * @param  \Kasi\Database\Connection  $connection
     * @param  string  $path
     * @return void
     */
    public function dump(Connection $connection, $path)
    {
        $commands = new Collection([
            $this->baseDumpCommand().' --schema-only > '.$path,
        ]);

        if ($this->hasMigrationTable()) {
            $commands->push($this->baseDumpCommand().' -t '.$this->getMigrationTable().' --data-only >> '.$path);
        }

        $commands->map(function ($command, $path) {
            $this->makeProcess($command)->mustRun($this->output, array_merge($this->baseVariables($this->connection->getConfig()), [
                'KASI_LOAD_PATH' => $path,
            ]));
        });
    }

    /**
     * Load the given schema file into the database.
     *
     * @param  string  $path
     * @return void
     */
    public function load($path)
    {
        $command = 'pg_restore --no-owner --no-acl --clean --if-exists --host="${:KASI_LOAD_HOST}" --port="${:KASI_LOAD_PORT}" --username="${:KASI_LOAD_USER}" --dbname="${:KASI_LOAD_DATABASE}" "${:KASI_LOAD_PATH}"';

        if (str_ends_with($path, '.sql')) {
            $command = 'psql --file="${:KASI_LOAD_PATH}" --host="${:KASI_LOAD_HOST}" --port="${:KASI_LOAD_PORT}" --username="${:KASI_LOAD_USER}" --dbname="${:KASI_LOAD_DATABASE}"';
        }

        $process = $this->makeProcess($command);

        $process->mustRun(null, array_merge($this->baseVariables($this->connection->getConfig()), [
            'KASI_LOAD_PATH' => $path,
        ]));
    }

    /**
     * Get the name of the application's migration table.
     *
     * @return string
     */
    protected function getMigrationTable(): string
    {
        [$schema, $table] = $this->connection->getSchemaBuilder()->parseSchemaAndTable($this->migrationTable);

        return $schema.'.'.$this->connection->getTablePrefix().$table;
    }

    /**
     * Get the base dump command arguments for PostgreSQL as a string.
     *
     * @return string
     */
    protected function baseDumpCommand()
    {
        return 'pg_dump --no-owner --no-acl --host="${:KASI_LOAD_HOST}" --port="${:KASI_LOAD_PORT}" --username="${:KASI_LOAD_USER}" --dbname="${:KASI_LOAD_DATABASE}"';
    }

    /**
     * Get the base variables for a dump / load command.
     *
     * @param  array  $config
     * @return array
     */
    protected function baseVariables(array $config)
    {
        $config['host'] ??= '';

        return [
            'KASI_LOAD_HOST' => is_array($config['host']) ? $config['host'][0] : $config['host'],
            'KASI_LOAD_PORT' => $config['port'] ?? '',
            'KASI_LOAD_USER' => $config['username'],
            'PGPASSWORD' => $config['password'],
            'KASI_LOAD_DATABASE' => $config['database'],
        ];
    }
}
