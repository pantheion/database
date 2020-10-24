<?php

namespace Pantheion\Database\Migration;

use Pantheion\Database\Query\Builder;
use Pantheion\Database\Table\Schema;
use Pantheion\Facade\Arr;
use Pantheion\Facade\Inflection;
use Pantheion\Facade\Str;
use Pantheion\Filesystem\Directory;
use Pantheion\Filesystem\File;
use Pantheion\Facade\Table;

/**
 * Manages the migrations ups and rollbacks
 */
class Manager
{
    /**
     * Path for the migrations folder
     */
    const MIGRATIONS_PATH = "database/migrations";

    /**
     * Array of migration files
     *
     * @var File[]
     */
    protected $files;

    /**
     * Number of the last batch inserted
     *
     * @var int
     */
    protected $lastBatch;

    /**
     * Migration Manager constructor function
     */
    public function __construct()
    {
        $this->files = $this->load();    
    }

    /**
     * Loads the migration files
     *
     * @return File[]
     */
    protected function load()
    {
        return Directory::get(Manager::MIGRATIONS_PATH)->files();
    }

    /**
     * Creates the migrations table if
     * it not yet exists
     *
     * @return void
     */
    protected function createMigrationsTable()
    {
        if (!Table::exists('migrations')) {
            Table::create('migrations', function(Schema $schema) {
                $schema->primary('id');
                $schema->varchar('file_name');
                $schema->integer('batch');
            });
        }
    }

    /**
     * Returns from the database all
     * the entries for migrations
     *
     * @return array
     */
    protected function migrations()
    {
        return (new Builder('migrations'))->get();
    }

    /**
     * Returns the number of the last batch
     *
     * @return int
     */
    protected function lastBatch()
    {
        if($this->lastBatch) {
            return $this->lastBatch;
        }

        $batches = (new Builder("migrations"))->select('batch')->orderBy('batch', true)->get();
        
        if(Arr::empty($batches)) {
            return $this->lastBatch = 0;
        }

        return $this->lastBatch = intval(Arr::first($batches));
    }

    /**
     * Returns the number of the next batch
     *
     * @return int
     */
    protected function nextBatch()
    {
        return $this->lastBatch() + 1;
    }

    /**
     * Returns an array of the last
     * performed migrations
     *
     * @return array
     */
    protected function lastBatchMigrations()
    {
        return (new Builder('migrations'))->where('batch', $this->lastBatch())->get();
    }

    /**
     * Returns the next migrations to perform
     *
     * @return File[]
     */
    protected function nextBatchMigrations()
    {
        $lastBatchMigrations = $this->lastBatchMigrations();
        
        if (Arr::empty($lastBatchMigrations)) {
            return $this->files;
        }

        $last = Arr::last($lastBatchMigrations);
        $lastIndex = array_search($last["file_name"], array_column($this->files, 'fullname'));

        return array_slice($this->files, $lastIndex + 1);
    }

    /**
     * Returns an instance of the migration
     *
     * @param File $migration
     * @return Migration
     */
    protected function instanciate(File $migration)
    {
        require_once $migration->fullpath;

        $class = Inflection::classerize(
            Str::after($migration->name, "_")
        );

        return new $class;
    }

    /**
     * Returns a file instance based
     * on the file name
     *
     * @param string $filename
     * @return File
     */
    protected function getFileFromName(string $filename)
    {
        $file = array_filter($this->files, function($file) use ($filename) {
            return $file->fullname === $filename;
        });

        return count($file) === 1 ? reset($file) : null; 
    }

    /**
     * Runs up the next batch
     *
     * @return void
     */
    public function runUp()
    {
        $this->createMigrationsTable();
        
        $nextBatch = $this->nextBatch();
        $nextBatchMigrations = $this->nextBatchMigrations();

        if(Arr::empty($nextBatchMigrations)) {
            echo "No migrations to run up" . PHP_EOL;
            return;
        }

        foreach($nextBatchMigrations as $migration) {
            echo "Migrating file '{$migration->fullname}'" . PHP_EOL;
            $this->instanciate($migration)->up();

            (new Builder('migrations'))->insert(['file_name' => $migration->fullname, 'batch' => $nextBatch]);
        }
    }

    /**
     * Runs up the next batch
     *
     * @return void
     */
    public function runDown()
    {
        $this->createMigrationsTable();

        $lastBatch = $this->nextBatch();
        $lastBatchMigrations = $this->nextBatchMigrations();

        if (Arr::empty($lastBatchMigrations)) {
            echo "No migrations to run down" . PHP_EOL;
            return;
        }

        foreach ($lastBatchMigrations as $migration) {
            echo "Rolling back migration file '{$migration->fullname}'" . PHP_EOL;
            $this->instanciate($migration)->down();
        }

        (new Builder('migrations'))->where('batch', $lastBatch)->delete();
    }

    /**
     * Rollsback all the migrations
     * already performed
     *
     * @return void
     */
    public function runDownAll()
    {
        $this->createMigrationsTable();

        $migrations = array_reverse($this->migrations());

        if(Arr::empty($migrations)) {
            echo "No migrations to rollback" . PHP_EOL;
            return;
        }

        foreach($migrations as $migration) {
            $filename = $migration["file_name"];
            $file = $this->getFileFromName($filename);
            
            if(!$file) {
                die("Migration file {$filename} was not found");
            }

            echo "Rolling back migration file '{$filename}'" . PHP_EOL;
            $instance = $this->instanciate($file);
            $instance->down();

            (new Builder('migrations'))->where('file_name', $filename)->delete();
        }
    }

    /**
     * Rollsback all the migration and
     * then runs up again another batch
     *
     * @return void
     */
    public function refresh()
    {
        $this->runDownAll();
        $this->runUp();
    }
}