<?php

namespace Kasi\Console;

use Kasi\Filesystem\Filesystem;

use function Kasi\Filesystem\join_paths;

abstract class MigrationGeneratorCommand extends Command
{
    /**
     * The filesystem instance.
     *
     * @var \Kasi\Filesystem\Filesystem
     */
    protected $files;

    /**
     * Create a new migration generator command instance.
     *
     * @param  \Kasi\Filesystem\Filesystem  $files
     * @return void
     */
    public function __construct(Filesystem $files)
    {
        parent::__construct();

        $this->files = $files;
    }

    /**
     * Get the migration table name.
     *
     * @return string
     */
    abstract protected function migrationTableName();

    /**
     * Get the path to the migration stub file.
     *
     * @return string
     */
    abstract protected function migrationStubFile();

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $table = $this->migrationTableName();

        if ($this->migrationExists($table)) {
            $this->components->error('Migration already exists.');

            return 1;
        }

        $this->replaceMigrationPlaceholders(
            $this->createBaseMigration($table), $table
        );

        $this->components->info('Migration created successfully.');

        return 0;
    }

    /**
     * Create a base migration file for the table.
     *
     * @param  string  $table
     * @return string
     */
    protected function createBaseMigration($table)
    {
        return $this->kasi['migration.creator']->create(
            'create_'.$table.'_table', $this->kasi->databasePath('/migrations')
        );
    }

    /**
     * Replace the placeholders in the generated migration file.
     *
     * @param  string  $path
     * @param  string  $table
     * @return void
     */
    protected function replaceMigrationPlaceholders($path, $table)
    {
        $stub = str_replace(
            '{{table}}', $table, $this->files->get($this->migrationStubFile())
        );

        $this->files->put($path, $stub);
    }

    /**
     * Determine whether a migration for the table already exists.
     *
     * @param  string  $table
     * @return bool
     */
    protected function migrationExists($table)
    {
        return count($this->files->glob(
            join_paths($this->kasi->databasePath('migrations'), '*_*_*_*_create_'.$table.'_table.php')
        )) !== 0;
    }
}
