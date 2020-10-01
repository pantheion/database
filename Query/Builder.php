<?php

namespace Pantheion\Database\Query;

use Pantheion\Facade\Arr;
use Pantheion\Facade\Str;
use Pantheion\Facade\Connection;

class Builder
{
    /**
     * Basic WHERE clause
     */
    const WHERE_BASIC = 1;

    /**
     * Nested WHERE Clause
     */
    const WHERE_NESTED = 2;

    /**
     * Between WHERE Clause
     */
    const WHERE_EXTRAS = [
        "between" => 3,
        "in" => 4,
        "null" => 5,
    ];

    /**
     * Possible booleans of WHERE clauses
     */
    const WHERE_BOOLEANS = [
        "and" => "AND",
        "or" => "OR"
    ];

    /**
     * Possible orders of WHERE clauses
     */
    const WHERE_ORDERS = [
        'asc' => "ASC",
        'desc' => "DESC"
    ];

    /**
     * Possible operators for
     * the WHERE clause
     */
    const OPERATORS = [
        '=', '!=', '<>', '<=>',
        '>', '>=', '<', '<=',
        'LIKE', 'NOT LIKE'
    ];

    /**
     * Table's name
     *
     * @var string
     */
    protected $table;

    /**
     * Selected columns
     *
     * @var array
     */
    protected $columns;

    /**
     * Whether the selection will
     * only retrieve distinct results
     *
     * @var boolean
     */
    protected $distinct;

    /**
     * Where clauses in array form
     *
     * @var array
     */
    protected $wheres;

    /**
     * Order the results in the
     * order of the columns
     * from this array
     *
     * @var array
     */
    protected $orderBy;

    /**
     * List of columns to group by
     * the results
     *
     * @var array
     */
    protected $groupBy;

    /**
     * List of havings for the
     * result
     *
     * @var array
     */
    protected $having;

    /**
     * Limits the number of results
     * 
     * @var int
     */
    protected $limit;

    /**
     * Value to offset the results
     * 
     * @var int
     */
    protected $offset;

    /**
     * List of joins for the query
     *
     * @var array
     */
    protected $joins;

    /**
     * Constructor function for Builder
     *
     * @param string $table
     */
    public function __construct(string $table)
    {      
        $this->table = $table;
        $this->columns = ['*'];
        $this->distinct = false;
        $this->wheres = [];
        $this->orderBy = [];
        $this->groupBy = [];
        $this->having = [];
        $this->limit = null;
        $this->offset = null;
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
        $this->columns = array_map(function($column) {
            return "`" . $column . "`";
        }, $columns);

        return $this;
    }

    /**
     * Sets the query to only select distinct
     * results from the table
     *
     * @return Builder
     */
    public function distinct()
    {
        $this->distinct = true;

        return $this;
    }

    /**
     * Adds a WHERE clause to the query.
     * Can be added as a Basic Where (eg. "where('name', '=', 'Charles')")
     * or as a Nested Where (eg. "where([ ['name', '=', 'Charles'], ['price', '<', 20.0] ])")
     *
     * @param ...$args WHERE conditions
     * @return Builder
     */
    public function where(...$args) 
    {
        $this->wheres[] = $this->resolveWhere(Builder::WHERE_BOOLEANS["and"], ...$args);

        return $this;
    }

    /**
     * Either resolves for a nested where
     * or a basic where, depending on the number
     * of arguments passed on the "where" function
     *
     * @param string $boolean whether it's an AND or OR where
     * @param ...$args conditions for the WHERE clause
     * @return array
     */
    public function resolveWhere(string $boolean, ...$args) 
    {
        switch(count($args)) {
            case 1:
                return $this->resolveNested($boolean, ...$args);
                break;
            case 2: case 3:
                return $this->resolveBasic($boolean, ...$args);
                break;
        }
    }

    /**
     * Resolves a nested WHERE clause
     *
     * @param string $boolean whether it's an AND or OR where
     * @param ...$args conditions for the WHERE clause
     * @return array
     */
    protected function resolveNested(string $boolean, ...$args)
    {
        list($nested) = $args;

        if($nested instanceof \Closure) {
            $nestedQuery = new Builder($this->table);
            
            $nested($nestedQuery);
            return [
                'type' => Builder::WHERE_NESTED,
                'boolean' => $boolean,
                'clauses' => $nestedQuery->wheres
            ];
        }

        if(!Arr::isMulti($nested)) {
            throw new \Exception("Incorrect array format for where conditions");
        }

        $clauses = [];
        foreach($nested as $condition) {
            $clauses[] = $this->resolveBasic(Builder::WHERE_BOOLEANS["and"], ...$condition);
        }

        return [
            'type' => Builder::WHERE_NESTED,
            'boolean' => $boolean,
            'clauses' => $clauses
        ];
    }

    /**
     * Resolves a basic WHERE clause
     *
     * @param string $boolean whether it's an AND or OR where
     * @param ...$args conditions for the WHERE clause
     * @return array
     */
    protected function resolveBasic(string $boolean, ...$args)
    {
        if(count($args) === 2) {
            list($column, $value) = $args;

            return [
                "type" => Builder::WHERE_BASIC,
                "column" => $column,
                "operator" => "=",
                "value" => $value,
                "boolean" => $boolean
            ];
        }

        list($column, $operator, $value) = $args;

        return [
            "type" => Builder::WHERE_BASIC,
            "column" => $column,
            "operator" => $operator,
            "value" => $value,
            "boolean" => $boolean
        ];
    }

    /**
     * Adds an OR WHERE clause to the query
     *
     * @param ...$args conditions for the WHERE clause
     * @return Builder
     */
    public function orWhere(...$args)
    {
        $this->wheres[] = $this->resolveWhere(Builder::WHERE_BOOLEANS["or"], ...$args);

        return $this;
    }

    /**
     * Sets the order of the results
     * by the column name provided
     *
     * @param string $column column's name
     * @param boolean $desc whether is sorted descending or not
     * @return Builder
     */
    public function orderBy(string $column, bool $desc = false) 
    {
        $this->orderBy[] = [
            "column" => $column,
            "order" => !$desc ? Builder::WHERE_ORDERS["asc"] : Builder::WHERE_ORDERS["desc"]
        ];

        return $this;
    }

    /**
     * Sets the group by option on the
     * where query results
     *
     * @param ...$args columns to perform the group by
     * @return Builder
     */
    public function groupBy(...$args)
    {
        $this->groupBy = Arr::merge($this->groupBy, $args);
        return $this;
    }

    /**
     * Adds an having clause to the query
     *
     * @param ...$args arguments to be used like the "where" method
     * @return Builder
     */
    public function having(...$args)
    {
        $this->having[] = $this->resolveWhere(Builder::WHERE_BOOLEANS["and"], ...$args);
        return $this;
    }

    /**
     * Sets the results offset
     *
     * @param integer $offset result's offset
     * @return Builder
     */
    public function offset(int $offset)
    {
        $this->offset = $offset;
        return $this;
    }

    /**
     * Sets the results limit
     *
     * @param integer $limit result's limit
     * @return Builder
     */
    public function limit(int $limit)
    {
        $this->limit = $limit;
        return $this;
    }

    /**
     * Adds an inner join to the query
     *
     * @param string $table table's name
     * @return Builder
     */
    public function join(string $table)
    {
        $this->joins[] = [
            "type" => "inner",
            "table" => $table
        ];

        return $this;
    }

    /**
     * Inserts an array of data in the
     * table.
     *
     * @param array $insert array of data
     * @return int last inserted id
     */
    public function insert(array $insert)
    {
        if(Arr::empty($insert)) {
            throw new \Exception("Insert array is empty");
        }

        if(!Arr::isAssoc($insert) && !is_array(reset($insert))) {
            throw new \Exception("Invalid insert array format");
        }

        if(!is_array(reset($insert))) {
            $insert = [$insert];
        }

        $columns = $this->insertColumns($insert);
        $values = $this->insertValues($insert, $columns);
        
        $sql = Connection::sql()->insert($this->table, $columns, count($insert));

        return Connection::execute($sql, $values);
    }

    /**
     * Retrieves the list of column names
     * from the insert statement
     *
     * @param array $insert array of data to be inserted
     * @return array array with the columns
     */
    protected function insertColumns(array $insert)
    {
        $columns = [];

        if(count($insert) > 1) {
            foreach($insert as $value) {
                $columns = array_merge($columns, array_keys($value));
            }

            $columns = array_unique($columns);
        } else {
            $columns = array_keys(reset($insert));
        }

        sort($columns);

        return $columns;
    }

    /**
     * Retrieves the list of column values
     * from the insert statement
     *
     * @param array $insert array of data to be inserted
     * @param array $columns array with the columns
     * @return array values to be inserted
     */
    protected function insertValues(array $insert, array $columns)
    {
        $values = [];

        foreach($insert as $insertion) {
            foreach($columns as $column) {
                $values[] = isset($insertion[$column]) ? $insertion[$column] : null;
            }
        }

        return $values;
    }

    /**
     * Updates the 
     *
     * @param array $update array with the columns and the updated values
     * @return void
     */
    public function update(array $update)
    {
        if (Arr::empty($update)) {
            throw new \Exception("Insert update is empty");
        }

        if(!Arr::isAssoc($update)) {
            throw new \Exception("Please provide an associative array where the keys are the columns");
        }

        list($whereQuery, $whereValues) = $this->toSqlWheres();
        $updateValues = Arr::merge(
            array_values($update) ?: [],
            $whereValues ?: [],
        );
        
        $sql = Connection::sql()->update($this->table, array_keys($update), $whereQuery ?: "");
        Connection::execute($sql, $updateValues);
    }

    public function delete()
    {
        list($whereQuery, $whereValues) = $this->toSqlWheres();

        $sql = Connection::sql()->delete($this->table, $whereQuery ?: "");
        Connection::execute($sql, $whereValues ?: []);
    }

    public function truncate()
    {

    }

    /**
     * Builds an SQL statement for a
     * WHERE query, while setting a temporary
     * "values" property to be used when getting
     * the results.
     */
    public function sql()
    {
        $dialect = Connection::sql();

        list($whereQuery, $whereValues) = $this->toSqlWheres();
        list($havingQuery, $havingValues) = $this->toSqlHaving();

        $this->values = Arr::merge(
            $whereValues ?: [],
            $havingValues ?: [] 
        );

        return $this->sql = sprintf(
            $dialect::SELECT,
            $this->distinct ? $dialect::DISTINCT : "",
            $this->toSqlColumns(),
            $this->toSqlTable(),
            $whereQuery,
            $this->toSqlGroupBy(),
            $havingQuery,
            $this->toSqlOrderBy(),
            $this->toSqlLimitOffset()
        );
    }

    /**
     * Returns the SQL for the column selection
     * 
     * @return string
     */
    protected function toSqlColumns()
    {
        return join(", ", $this->columns);
    }

    /**
     * Returns the SQL for the table selection
     *
     * @return string
     */
    protected function toSqlTable()
    {
        return "`" . $this->table . "`";
    }

    /**
     * Returns the SQL and the values for
     * the finalized Where clauses
     * 
     * @return array
     */
    protected function toSqlWheres()
    {
        if(Arr::empty($this->wheres)) {
            return "";
        }

        unset($this->wheres[0]["boolean"]);

        $toSqlWheres = [];
        foreach($this->wheres as $where) {
            switch($where["type"]) {
                case Builder::WHERE_BASIC:
                    $toSqlWheres[] = $this->toSqlWhereBasic($where);
                    break;
                case Builder::WHERE_NESTED:
                    $toSqlWheres[] = $this->toSqlWhereNested($where);
                    break;
                case Builder::WHERE_EXTRAS["between"]:
                case Builder::WHERE_EXTRAS["in"]:
                case Builder::WHERE_EXTRAS["null"]:
                    $toSqlWheres[] = $this->toSqlWhereExtra($where);
                    break;
            }
        }

        return [
            $this->toSqlWhereGetQuery($toSqlWheres),
            $this->toSqlWhereGetValues($toSqlWheres)
        ];
    }

    /**
     * Returns the SQL for a Where Basic clause
     * 
     * @return array
     */
    protected function toSqlWhereBasic(array $where)
    {
        $column = "`" . $where["column"] . "`";

        if(!array_key_exists("boolean", $where)) {
            return [
                "query" => sprintf(
                    "%s %s ?",
                    $column,
                    $where["operator"]
                ),
                "value" => [$where["value"]]
            ];
        }

        if($where["boolean"] === Builder::WHERE_BOOLEANS["or"]) {
            return [
                "query" => sprintf(
                    Connection::sql()::WHERE_OR,
                    $column . " " . $where["operator"] . " ?"
                ),
                "value" => [$where["value"]]
            ]; 
        }

        return [
            "query" => sprintf(
                Connection::sql()::WHERE_AND,
                $column . " " . $where["operator"] . " ?"
            ),
            "value" => [$where["value"]]
        ]; 
    }

    /**
     * Returns the SQL for a Where Nested clause
     * 
     * @return array
     */
    protected function toSqlWhereNested(array $where) 
    {
        unset($where["clauses"][0]["boolean"]);
        
        $clauses = [];
        foreach($where["clauses"] as $clause) {
            $clauses[] = $this->toSqlWhereBasic($clause);
        }

        if(!array_key_exists("boolean", $where)) {
            return [
                "query" => sprintf("(%s)", join(" ", array_column($clauses, "query"))),
                "value" => array_column($where["clauses"], "value")
            ];
        }

        if($where["boolean"] === Builder::WHERE_BOOLEANS["or"]) {
            return [
                "query" => sprintf(
                    Connection::sql()::WHERE_OR,
                    "(" . join(" ", array_column($clauses, "query")) . ")"
                ),
                "value" => array_column($where["clauses"], "value")
            ]; 
        }

        return [
            "query" => sprintf(
                Connection::sql()::WHERE_AND,
                "(" . join(" ", array_column($clauses, "query")) . ")"
            ),
            "value" => array_column($where["clauses"], "value")
        ]; 
    }

    /**
     * Returns the SQL for a Where Extra clause
     * 
     * @return array
     */
    protected function toSqlWhereExtra(array $where)
    {
        $whereType = $this->toSqlWhereExtraType($where);
        
        $column = "`" . $where["column"] . "`";

        if(!array_key_exists("boolean", $where)) {
            return [
                "query" => $column . " " . $whereType,
                "value" => [isset($where["values"]) ? $where["values"] : null]
            ];
        }

        if($where["boolean"] === Builder::WHERE_BOOLEANS["or"]) {
            return [
                "query" => sprintf(
                    Connection::sql()::WHERE_OR,
                    $column . " " . $whereType
                ),
                "value" => [isset($where["values"]) ? $where["values"] : null]
            ]; 
        }

        return [
            "query" => sprintf(
                Connection::sql()::WHERE_AND,
                $column . " " . $whereType
            ),
            "value" => [isset($where["values"]) ? $where["values"] : null]
        ]; 
    }

    /**
     * Gets the SQL belonging to the type of the
     * Extra Where in case
     * 
     * @return string
     */
    protected function toSqlWhereExtraType(array $where)
    {
        if($where["type"] === Builder::WHERE_EXTRAS["between"]) {
            return $where["not"] ? Connection::sql()::WHERE_NOT_BETWEEN : Connection::sql()::WHERE_BETWEEN;
        } else if($where["type"] === Builder::WHERE_EXTRAS["in"]) {
            $whereInPlaceholders = "(" . join(",", array_pad(array(), count($where["values"]), "?")) . ")";

            return $where["not"] ?
                sprintf(Connection::sql()::WHERE_NOT_IN, $whereInPlaceholders) : 
                sprintf(Connection::sql()::WHERE_IN, $whereInPlaceholders);
        } else if($where["type"] === Builder::WHERE_EXTRAS["null"]) {
            return $where["not"] ? Connection::sql()::WHERE_NOT_NULL : Connection::sql()::WHERE_NULL;
        }
    }

    /**
     * Returns the SQL of the finalzied Where clause
     * 
     * @return string
     */
    protected function toSqlWhereGetQuery(array $where, bool $having = false)
    {
        return sprintf(
            $having ? Connection::sql()::HAVING : Connection::sql()::WHERE,
            join(" ", array_column($where, "query"))
        );
    }

    /**
     * Gets the values of the finalized Where clause
     * 
     * @return array
     */
    protected function toSqlWhereGetValues(array $where)
    {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveArrayIterator(
                Arr::merge([], array_column($where, "value"))
            )
        );
        
        $values = [];
        foreach ($iterator as $v) {
            if(is_null($v)) {
                continue;
            }

            $values[] = $v;
        }
        return $values;
    }

    /**
     * Returns the SQL for the Group By clause
     * 
     * @return string
     */
    protected function toSqlGroupBy()
    {
        if(Arr::empty($this->groupBy)) {
            return "";
        }

        $groupBy = join(", ", array_map(function($column) {
            return "`" . $column . "`";
        }, $this->groupBy));

        return sprintf(
            Connection::sql()::GROUP_BY,
            $groupBy
        );
    }

    /**
     * Returns the SQL for the Order By clause
     * 
     * @return string
     */
    protected function toSqlOrderBy()
    {
        if(Arr::empty($this->orderBy)) {
            return "";
        }

        $orderBy = join(", ", array_map(function($orderBy) {
            return "`{$orderBy['column']}` {$orderBy['order']}";
        }, $this->orderBy));

        return sprintf(
            Connection::sql()::ORDER_BY,
            $orderBy
        );
    }

    /**
     * Returns the SQL for the having clause
     *
     * @return string
     */
    protected function toSqlHaving()
    {
        if (Arr::empty($this->having)) {
            return "";
        }

        unset($this->having[0]["boolean"]);

        $toSqlHaving = [];
        foreach ($this->having as $having) {
            switch ($having["type"]) {
                case Builder::WHERE_BASIC:
                    $toSqlHaving[] = $this->toSqlWhereBasic($having);
                    break;
                case Builder::WHERE_NESTED:
                    $toSqlHaving[] = $this->toSqlWhereNested($having);
                    break;
                case Builder::WHERE_EXTRAS["between"]:
                case Builder::WHERE_EXTRAS["in"]:
                case Builder::WHERE_EXTRAS["null"]:
                    $toSqlHaving[] = $this->toSqlWhereExtra($having);
                    break;
            }
        }

        return [
            $this->toSqlWhereGetQuery($toSqlHaving, true),
            $this->toSqlWhereGetValues($toSqlHaving)
        ];
    }

    /**
     * Returns the SQL for the limit and offset
     * 
     * @return string
     */
    protected function toSqlLimitOffset()
    {
        return sprintf(
            "%s %s",
            !is_null($this->limit) ? sprintf(Connection::sql()::LIMIT, $this->limit) : null,
            !is_null($this->offset) ? sprintf(Connection::sql()::OFFSET, $this->offset) : null
        );
    }

    /**
     * Returns the results from the
     * query
     * 
     * @return array
     */
    public function get()
    {
        return Connection::execute($this->sql(), $this->values);
    }

    /**
     * Returns the amount of results
     * that resulted from the query
     * 
     * @return int
     */
    public function count()
    {
        $this->columns = ["COUNT(*) as count"];

        return intval($this->get()[0]["count"]);
    }

    /**
     * Checks whether ther are results for
     * the query
     * 
     * @return boolean
     */
    public function exists()
    {
        return $this->count() > 0;
    }

    /**
     * Checks whether there aren't any
     * results for the query
     * 
     * @return boolean
     */
    public function doesNotExist()
    {
        return $this->count() < 1;
    }

    /**
     * Returns the average of a column
     * from the results of the query.
     * The result is in a string format
     * 
     * @param string $column column to perform the average
     * @return string
     */
    public function avg(string $column)
    {
        $this->columns = [
            sprintf("AVG(`%s`) as avg", $column)
        ];

        return $this->get()[0]["avg"];
    }

    /**
     * Returns the min value from a column
     * of the results of the query. The result
     * is in a string format.
     * 
     * @param string $column column to get the min value
     * @return string
     */
    public function min(string $column)
    {
        $this->columns = [
            sprintf("MIN(`%s`) as min", $column)
        ];

        return $this->get()[0]["min"];
    }

    /**
     * Returns the max value from a column
     * of the results of the query. The result
     * is in a string format.
     * 
     * @param string $column column to get the max value
     * @return string
     */
    public function max(string $column)
    {
        $this->columns = [
            sprintf("MAX(`%s`) as max", $column)
        ];

        return $this->get()[0]["max"];
    }

    /**
     * Returns the values from a certain
     * column from the results of the query
     * 
     * @param string $column
     * @return array
     */
    public function pluck(string $column)
    {
        return array_column(
            $this->select($column)->get(),
            $column
        );
    }

    /**
     * Dies and dumps the results
     * from the query
     * 
     * @return void
     */
    public function dd()
    {
        dd($this->get());
    }

    /**
     * Returns the first result from
     * the query or null if the results
     * are empty.
     *
     * @return array|null
     */
    public function first()
    {
        $this->limit(1);
        $result = $this->get();

        return count($result) > 0 ? $result[0] : null;
    }

    /**
     * Finds a single row of results
     * based on an id passed
     */
    public function find(int $id)
    {
        return $this->where('id', $id)->get();
    }

    /**
     * PHP's magic method to resolve
     * method calls
     *
     * @param string $name method's name
     * @param array $args methods' parameters
     * @return mixed
     */
    public function __call(string $name, array $args)
    {
        $wheres = ['Between', 'In', 'Null'];

        foreach($wheres as $where) {
            if(
                (Str::contains($name, "where") || Str::contains($name, "orWhere")) &&
                Str::endsWith($name, $where)
            ) {
                return $this->resolveExtraWhere($where, $name, $args);
            }
        }

        throw new \Exception("Method '{$name}' not found");
    }

    /**
     * Resolves the call of one of the
     * extra WHERE methods.
     *
     * @param string $methodName extra where method called
     * @param string $name name of the method called
     * @param array $args where conditions
     * @return Builder
     */
    protected function resolveExtraWhere(string $methodName, string $name, array $args)
    {
        $method = "resolve" . $methodName;
        $identifier = strtolower($methodName);

        $this->validateExtraWhere($identifier, $args);

        if(count($args) === 2) {
            list($column, $values) = $args; 
        } else if($identifier === "null" && count($args) === 1) {
            list($column) = $args;
            $values = null;
        }

        if(Str::endsWith($name, 'Not' . $methodName)) {
            if(Str::startsWith($name, 'or')) {                    
                $this->wheres[] = [
                    "type" => Builder::WHERE_EXTRAS[$identifier],
                    "column" => $column,
                    "values" => $values,
                    "boolean" => Builder::WHERE_BOOLEANS["or"],
                    "not" => true
                ];
                return $this;
            }
                    
            $this->wheres[] = [
                "type" => Builder::WHERE_EXTRAS[$identifier],
                "column" => $column,
                "values" => $values,
                "boolean" => Builder::WHERE_BOOLEANS["and"],
                "not" => true
            ];
            return $this;
        }
            
        if(Str::startsWith($name, 'or')) {
            $this->wheres[] = [
                "type" => Builder::WHERE_EXTRAS[$identifier],
                "column" => $column,
                "values" => $values,
                "boolean" => Builder::WHERE_BOOLEANS["or"],
                "not" => false
            ];
            return $this;
        }
                
        $this->wheres[] = [
            "type" => Builder::WHERE_EXTRAS[$identifier],
            "column" => $column,
            "values" => $values,
            "boolean" => Builder::WHERE_BOOLEANS["and"],
            "not" => false
        ];
        return $this;
    }

    /**
     * Checks for every possible error that
     * may occur calling one of the extra
     * where methods
     *
     * @param string $identifier extra where method identifier
     * @param array $args parameters passed
     * @return void
     */
    protected function validateExtraWhere($identifier, $args)
    {
        if($identifier === "null") {
            if(count($args) !== 1) {
                throw new \Exception("The whereNull type methods only require one parameter, the column name");
            }

            if(!is_string($args[0])) {
                throw new \Exception("Please provide a column name for the query as the first parameter");
            }
        } else {
            if(count($args) < 2) {
                throw new \Exception("The whereNull type methods requires two parameteres, the column name and an array of values");
            }

            if(!is_string($args[0])) {
                throw new \Exception("Please provide a column name for the query as the first parameter");
            }

            if(!is_array($args[1])) {
                throw new \Exception("Please provide an array of values as the second parameter");
            }
        }
    }
}
