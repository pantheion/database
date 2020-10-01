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
        $schema->comment('TABLE;');
        $schematic($schema);

        $sql = sprintf(
            Connection::sql()::CREATE_TABLE,
            $table,
            $schema->toSql($table),
            $schema->charset,
            $schema->collation,
            $schema->comment
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
     * @return Table[] array of Table
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

    /**
     * Creates a pivot table between two tables
     *
     * @param string $first first table
     * @param string $second second table
     * @param \Closure $schematic schematic for the schema
     * @param string $alias custom name for the table
     * @return Table pivot table
     */
    public function pivot(string $first, string $second, \Closure $schematic = null, string $alias = null)
    {
        if (!$this->exists($first) || !$this->exists($second)) {
            throw new \Exception("One of the tables to be pivoted doesn't exist");
        }

        $name = [Inflection::singularize($first), Inflection::singularize($second)];
        sort($name);
        
        list($firstColumn, $secondColumn) = [
            $name[0] . "_id",
            $name[1] . "_id",
        ];

        $name = !is_null($alias) ? $alias : implode("_", $name);
        if($this->exists($name)) {
            throw new \Exception("The table {$name} already exists");
        }

        $schema = new Schema($name);
        if(is_null($schematic)) {
            $schematic = function(Schema $schema) use ($first, $firstColumn, $second, $secondColumn) {
                $schema->primary();
                $schema->foreign($firstColumn, $first);
                $schema->foreign($secondColumn, $second);
            };

            $schematic($schema);
        } else {
            $schematic($schema);
            $schema->foreign($firstColumn, $first);
            $schema->foreign($secondColumn, $second);
        }

        $schema->comment('PIVOT;');
        $sql = sprintf(
            Connection::sql()::CREATE_TABLE,
            $name,
            $schema->toSql(),
            $schema->charset,
            $schema->collation,
            $schema->comment
        );

        Connection::execute($sql);
        return static::$tables[$name] = new Table($name, $schema);
    }

    /**
     * Returns a Query Builder instance
     * to use on a table with the name
     * passed as argument
     *
     * @param string $table table's name
     * @return Builder
     */
    public function query(string $table)
    {
        if (!$this->exists($table)) {
            throw new \Exception("Table `{$table}` does not exist");
        }

        return new Builder($table);
    }
}