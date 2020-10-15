<?php

namespace Pantheion\Database\Query;

use Pantheion\Facade\Connection;

/**
 * Query Builder specifically made for
 * join clauses
 */
class JoinBuilder extends Builder
{
    /**
     * Original query's table name
     *
     * @var string
     */
    protected $baseTable;

    /**
     * Constructor function for JoinBuilders
     *
     * @param string $table table name to be joined
     * @param string $baseTable name of the original query's table
     */
    public function __construct(string $table, string $baseTable)
    {
        parent::__construct($table);
        $this->baseTable = $baseTable;
        $this->on = [];

        $this->on('id', 'id');
    }

    /**
     * Adds the ON clause to the query
     *
     * @param string $column joined table's column
     * @param string $baseColumn base table's column
     * @return JoinBuilder
     */
    protected function on(string $column, string $baseColumn)
    {
        $this->on = [
            "`{$this->table}`.`{$column}`",
            "=",
            "`{$this->baseTable}`.`{$baseColumn}`"
        ];

        return $this;
    }

    /**
     * Specifies the columns to be selected
     * from the results
     *
     * @param ...$columns columns to select from the results
     * @return Builder
     */
    public function select(...$columns)
    {
        $this->columns = [];
        $this->columns[$this->table] = array_map(function ($column) {
            return [
                "name" => $column,
                "alias" => "{$this->table}_{$column}",
            ];
        }, $columns);

        return $this;
    }

    /**
     * TODO
     *
     * @param string $column join statement's column
     * @param string $baseColumn original query's column
     * @return JoinBuilder
     */
    public function compareToBaseColumn(string $column, string $baseColumn)
    {
        $this->where[] = $this->where($column, $baseColumn);

        return $this;
    }

    /**
     * Returns the SQL for the join statement
     *
     * @return string
     */
    public function toSqlJoin()
    {
        return sprintf(
            Connection::sql()::JOIN,
            "`{$this->table}`",
            sprintf(Connection::sql()::JOIN_ON, join(" ", $this->on))
        );
    }

    /**
     * Returns the columns selected
     *
     * @return array
     */
    public function getJoinColumns()
    {
        return $this->columns;
    }

    /**
     * Returns the wheres on this
     * join statement
     *
     * @return array
     */
    public function getJoinWheres()
    {
        return $this->wheres;
    }
}