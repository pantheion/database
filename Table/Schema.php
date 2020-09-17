<?php

namespace Pantheion\Database\Table;

use Pantheion\Facade\Connection;
use Pantheion\Database\Dialect\Sql;

/**
 * Represents a table's Schema
 */
class Schema
{
    use ColumnDefinitions;

    /**
     * Schema's charset
     *
     * @var string
     */
    public $charset = "utf8mb4";

    /**
     * Schema's collation
     *
     * @var string
     */
    public $collation = "utf8mb4_unicode_ci";

    /**
     * Table name
     *
     * @var string
     */
    public $table;

    /**
     * Schema's columns
     *
     * @var Column[]
     */
    public $columns;

    /**
     * Constructor function for Schema
     *
     * @param string $table table's name
     */
    public function __construct(string $table)
    {
        $this->table = $table;
        $this->columns = [];    
    }

    /**
     * Sets the schema's charset
     *
     * @param string $charset
     * @return Schema
     */
    public function charset(string $charset)
    {
        $this->charset = $charset;
        return $this;
    }

    /**
     * Set's the Schema's collation
     *
     * @param string $collation
     * @return Schema
     */
    public function collation(string $collation)
    {
        $this->collation = $collation;
        return $this;
    }

    /**
     * Checks if the Schema has a column with
     * the name passed as argument
     *
     * @param string $name name of the column
     * @return boolean existance of column in the Schema
     */
    public function hasColumn(string $name)
    {
        foreach($this->columns as $column)
        {
            if($column->name === $name) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns a column from the Schema
     *
     * @param string $name
     * @return Column
     */
    public function getColumn(string $name)
    {
        foreach($this->columns as $column)
        {
            if($column->name == $name) {
                return $column;
            }
        }

        throw new \Exception("This schema doesn't a column named {$name}");
    }

    /**
     * Converts the schema to SQL
     *
     * @return string Schema's SQL representation
     */
    public function toSql()
    {
        $columnsSql = [];
        foreach($this->columns as $column) {
            $columnsSql[] = $column->toSql($this->table, Connection::sql());
        }
        
        return join(", ", $columnsSql);
    }
}