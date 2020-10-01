<?php

namespace Pantheion\Database\Table;

use Pantheion\Database\Query\Builder;
use Pantheion\Facade\Connection;
use Pantheion\Facade\Inflection;
use Pantheion\Facade\Str;
use Pantheion\Facade\Table as Manager;

/**
 * Represents a SQL table
 */
class Table
{
    /**
     * Table's name
     *
     * @var string
     */
    public $name;

    /**
     * Table's correspondent Model
     *
     * @var string
     */
    public $model;

    /**
     * Table's full Model's class
     *
     * @var string
     */
    public $class;

    /**
     * Table's schema
     *
     * @var Schema
     */
    public $schema;

    /**
     * Namespace where the Models are placed
     */
    protected const MODEL_NAMESPACE = "App\\Model\\";

    /**
     * Constructor function for Table
     *
     * @param string $name name of the table
     * @param Schema $schema Schema instance for the table
     */
    public function __construct(string $name, Schema $schema = null)
    {
        $this->name = $name;
        $this->model = Inflection::classerize($name);
        $this->class = Table::MODEL_NAMESPACE . $this->model;
        
        $this->schema = $schema ?: $this->resolveSchema();
    }

    /**
     * Builds a Schema instance based on the
     * table's name
     *
     * @return Schema table's Schema
     */
    protected function resolveSchema()
    {
        $columnsSql = Connection::sql()->columns($this->name);
        $columns = Connection::execute($columnsSql);

        $schemaColumns = $this->resolveSchemaColumns($columns);
        
        $schema = new Schema($this->name);
        foreach($schemaColumns as $schemaColumn) {
            $schema->add($schemaColumn);
        }

        $tableSql = Connection::sql()->table($this->name);
        $table = Connection::execute($tableSql)[0];
        
        $schema->charset($table["CHARACTER_SET_NAME"]);
        $schema->collation($table["COLLATION_NAME"]);
        $schema->comment($table["TABLE_COMMENT"]);

        return $schema;
    }

    /**
     * Creates all the columns for the
     * table's Schema.
     *
     * @param array $columns raw column's data array
     * @return array
     */
    protected function resolveSchemaColumns(array $columns)
    {
        return array_map(function($column) {
            return Column::createFromArray($column);
        }, $columns);
    }

    /**
     * Adds columns to an existing table
     *
     * @param \Closure $schematic closure which executes the creation of the new columns
     * @return Table
     */
    public function addColumns(\Closure $schematic)
    {
        $schema = new Schema($this->name);
        $schematic($schema);

        $columnsSql = join(", ", array_map(function($column) {
            return sprintf(Connection::sql()::ADD_COLUMN, $column->toSql());
        }, $schema->columns));

        $sql = Connection::sql()->addColumns($this->name, $columnsSql);
        Connection::execute($sql);

        return $this;
    }

    /**
     * Checks if the table has a certain column
     *
     * @param string $name column's name
     * @return boolean
     */
    public function hasColumn(string $name)
    {
        $names = array_column($this->schema->columns, 'name');
        return in_array($name, $names);
    }

    /**
     * Renames a column from an existing table
     *
     * @param string $from old name
     * @param string $to new name
     * @return Table
     */
    public function renameColumn(string $from, string $to)
    {
        if(!$this->schema->hasColumn($from)) {
            throw new \Exception("Table {$this->name} doens't have a column {$from}");
        }

        $column = $this->schema->getColumn($from);
        $columnSql = Str::replaceFirst($from, $to, $column->toSql());

        $sql = Connection::sql()->renameColumn($this->name, $from, $columnSql);
        Connection::execute($sql);

        return $this;
    }

    /**
     * Alters existing columns of an existing table
     *
     * @param \Closure $schematic closure that executes the column changes
     * @return Table
     */
    public function alterColumns(\Closure $schematic)
    {
        $schema = new Schema($this->name);
        $schematic($schema);

        $name = $this->name;
        $oldSchema = $this->schema;

        $columns = join(", ", array_map(function ($column) use ($name, $oldSchema) {
            if (!$oldSchema->hasColumn($column->name)) {
                throw new \Exception("Table {$name} doesn't have the column {$column->name}");
            }
            
            $oldColumn = $oldSchema->getColumn($column->name);
            return sprintf(Connection::sql()::MODIFY_COLUMN, $oldColumn->difference($column));
        }, $schema->columns));

        $sql = Connection::sql()->alterColumns($this->name, $columns);
        Connection::execute($sql);

        return $this;
    }

    /**
     * Drops a column from the table with the
     * name passed as parameter
     *
     * @param string $name
     * @return Table
     */
    public function dropColumn(string $name)
    {
        if(!$this->hasColumn($name)) {
            throw new \Exception("Column '{$name}' doesn't exist in this table");
        }

        $column = $this->schema->getColumn($name);
        $transactions = [];

        if($column->unique) {
            $transactions[] = sprintf(
                Connection::sql()::ALTER_TABLE,
                $this->name,
                Connection::sql()->uniqueConstraintDrop($this->name, $column->name)
            );
        }

        if ($column->foreign) {
            $transactions[] = sprintf(
                Connection::sql()::ALTER_TABLE,
                $this->name,
                Connection::sql()->foreignConstraintDrop($this->name, $column->name)
            );
        }

        if ($column->primary) {
            $transactions[] = sprintf(
                Connection::sql()::ALTER_TABLE,
                $this->name,
                Connection::sql()->primaryConstraintDrop()
            );
        }

        $transactions[] = sprintf(
            Connection::sql()::ALTER_TABLE,
            $this->name,
            Connection::sql()->dropColumn($column->name)
        );

        $sql = join("; ", $transactions);
        Connection::execute($sql);

        return $this;
    }

    public function truncate()
    {
        $sql = sprintf(Connection::sql()::TRUNCATE_TABLE, $this->name);
        Connection::execute($sql);
    }

    /**
     * Checks if the table is a pivot table
     *
     * @return boolean whether the table is a pivot
     */
    public function isPivot() 
    {
        return Str::contains($this->schema->comment, "PIVOT;");
    }

    /**
     * Gets an array of data of the tables
     * that this table has a BelongsTo relation
     *
     * @return array
     */
    public function belongsTo()
    {
        $sql = Connection::sql()->belongsTo($this->name);
        $tables = Connection::execute($sql);

        // dd($tables);
        return array_map(function($table) {
            return new Table($table["TABLE_NAME"]);
        }, $tables);

        // return $tables;
    }

    /**
     * Gets an array of data of the tables
     * that this table has a HasMany relation
     *
     * @return array
     */
    public function hasMany()
    {
        $sql = Connection::sql()->hasMany($this->name);
        return Connection::execute($sql);
    }
}