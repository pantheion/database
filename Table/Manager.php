<?php

namespace Pantheion\Database\Table;

use Pantheion\Database\Query\Builder;
use Pantheion\Facade\Connection;
use Pantheion\Facade\Inflection;
use Pantheion\Facade\Str;

/**
 * Manages the database's tables
 */
class Manager
{
    /**
     * Lists the tables already used in the app
     *
     * @var Table[]
     */
    protected static $tables = [];

    /**
     * Creates a database table
     *
     * @param string $table table's name
     * @param \Closure $schematic schematic for the table
     * @return Table
     */
    public function create(string $table, \Closure $schematic)
    {
        if($this->exists($table)) {
            throw new \Exception("Table `{$table}` already exists");
        }

        $table = Inflection::tablerize($table);

        $schema = new Schema($table);
        $schematic($schema);

        $sql = sprintf(
            Connection::sql()::CREATE_TABLE,
            $table,
            $schema->toSql($table),
            $schema->charset,
            $schema->collation
        );

        Connection::execute($sql);
        return static::$tables[$table] = new Table($table, $schema);
    }

    /**
     * Checks if a table exists or not
     *
     * @param string $table table's name
     * @return boolean table exists or not
     */
    public function exists(string $table)
    {
        $sql = Connection::sql()->exists($table);
        $count = Connection::execute($sql);

        return boolval($count[0]["count"]);
    }

    /**
     * Selects a Table instance to work with
     *
     * @param string $table table's name
     * @return Table Table instance
     */
    public function use(string $table)
    {
        if(array_key_exists($table, static::$tables)) {
            return static::$tables[$table];
        }

        if (!$this->exists($table)) {
            throw new \Exception("Table `{$table}` does not exist");
        }

        return static::$tables[$table] = new Table($table);
    }

    /**
     * Gets all the tables present in the database
     *
     * @param boolean $pivots include or not include pivots
     * @param array $except tables to exclude
     * @return array array of Table
     */
    public function all(bool $pivots = false, array $except = [])
    {
        $sql = Connection::sql()->tables();
        $result = Connection::execute($sql);

        $result = array_filter($result, function ($table) use ($except) {
            return !in_array($table["TABLE_NAME"], $except);
        });

        if(!$pivots) {
            $result = array_filter($result, function($table) {
                return !Str::contains($table["TABLE_COMMENT"], "PIVOT;");
            });
        }

        return array_map(function($table) {
            return new Table($table["TABLE_NAME"]);
        }, $result);
    }

    /**
     * Drops an existing table
     *
     * @param string $table table's name
     * @return void
     */
    public function drop(string $table)
    {
        if (!$this->exists($table)) {
            throw new \Exception("Table `{$table}` does not exist");
        }

        $sql = sprintf(Connection::sql()::DROP_TABLE, $table);
        return Connection::execute($sql);
    }

    public function query(string $table)
    {
        return new Builder($table);
    }
}