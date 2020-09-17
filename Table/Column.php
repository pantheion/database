<?php

namespace Pantheion\Database\Table;

use Pantheion\Facade\Connection;
use Pantheion\Database\Type\Type;
use Pantheion\Facade\Str;

/**
 * Representation of a column from a
 * table's schema
 */
class Column
{
    /**
     * Column's name
     *
     * @var string
     */
    public $name;

    /**
     * Column's Type instance
     *
     * @var Type
     */
    public $type;

    /**
     * Column's options
     *
     * @var array
     */
    public $options;

    /**
     * Column's nullable state
     *
     * @var boolean
     */
    public $nullabe;

    /**
     * Column's default value
     *
     * @var mixed
     */
    public $default;

    /**
     * Column's auto increment state
     *
     * @var boolean
     */
    public $autoIncrement;

    /**
     * Column's after this column
     *
     * @var string
     */
    public $after;

    /**
     * Column is primary key
     *
     * @var boolean
     */
    public $primary;

    /**
     * Column has foreign key
     *
     * @var array
     */
    public $foreign;

    /**
     * Column is unique
     *
     * @var boolean
     */
    public $unique;

    /**
     * Constructor function of a column
     *
     * @param string $name column's name
     * @param string $type column's type
     * @param array $options column's options
     */
    public function __construct(string $name, string $type, array $options = null)
    {
        $this->name = $name;
        $this->type = Type::get($type);
        $this->options = $options;
        $this->nullable = null;
        $this->default = null;
        $this->autoIncrement = null;
        $this->after = null;
        $this->primary = null;
        $this->foreign = null;
        $this->unique = null;
    }

    /**
     * Sets the column as nullable
     *
     * @return Column
     */
    public function nullable()
    {
        $this->nullable = true;
        return $this;
    }

    /**
     * Sets a default value for the column
     *
     * @param $value
     * @return Column
     */
    public function default($value)
    {
        $this->default = $value;
        return $this;
    }

    /**
     * Sets the column to be auto incremented
     *
     * @return Column
     */
    public function autoIncrement()
    {
        $this->autoIncrement = true;
        return $this;
    }

    /**
     * Sets the column to be placed
     * after another one
     *
     * @param string $after column to be placed after
     * @return Column
     */
    public function after(string $after)
    {
        $this->after = $after;
        return $this;
    }

    /**
     * Sets the column as Primary Key
     *
     * @return Column
     */
    public function primary()
    {
        $this->primary = true;
        return $this;
    }

    /**
     * Sets the column to be a
     * Foreign Key
     *
     * @param string $foreign foreign table
     * @param string $column foreign column
     * @return Column
     */
    public function foreign(string $foreign, string $column)
    {
        $this->foreign = [
            "foreign" => $foreign,
            "column" => $column
        ];
        return $this;
    }

    /**
     * Sets the column to be unique
     *
     * @return Column
     */
    public function unique()
    {
        $this->unique = true;
    }

    /**
     * Creates a column based on an array of
     * information retrieved by SQL
     *
     * @param array $array raw information 
     * @return Column new column instance
     */
    public static function createFromArray(array $array)
    {        
        $column = new Column(
            $array["COLUMN_NAME"],
            Type::getClassFromDataType($array["DATA_TYPE"]),
            static::resolveArrayOptions($array)
        );

        if ($array["IS_NULLABLE"] === "YES") {
            $column->nullable();
        }

        if ($array["COLUMN_DEFAULT"]) {
            $column->default($column->type->toCodeValue($array["COLUMN_DEFAULT"]));
        }

        if ($array["EXTRA"] === "auto_increment") {
            $column->autoIncrement();
        }

        $column = static::resolveArrayKeys($array, $column);

        return $column;
    }

    /**
     * Builds the options array based on the
     * raw information retrieved by SQL
     *
     * @param array $array raw information
     * @return array options array
     */
    protected static function resolveArrayOptions(array $array)
    {
        $options = [];
        if(Str::contains($array["COLUMN_TYPE"], "unsigned")) {
            $options["unsigned"] = true;
        }

        if ($array["CHARACTER_MAXIMUM_LENGTH"]) {
            $options["length"] = $array["CHARACTER_MAXIMUM_LENGTH"];
        }

        if ($array["NUMERIC_PRECISION"]) {
            $options["length"] = $array["NUMERIC_PRECISION"];
        }

        if ($array["NUMERIC_SCALE"]) {
            $options["precision"] = $array["NUMERIC_SCALE"];
        }

        return $options;
    }

    /**
     * Sets the constraints based on the
     * raw information retrieved by SQL
     *
     * @param array $array
     * @return Column Column instance
     */
    protected static function resolveArrayKeys(array $array, Column $column)
    {
        if (Str::contains($array["COLUMN_KEY"], "PRI")) 
        {
            $column->primary();
        }

        if (Str::contains($array["COLUMN_KEY"], "MUL")) 
        {
            $sql = Connection::sql()->foreignConstraintName($array["TABLE_NAME"], $array["COLUMN_NAME"]);
            $result = Connection::execute($sql)[0];
            
            $column->foreign(
                $result["REFERENCED_TABLE_NAME"],
                $result["REFERENCED_COLUMN_NAME"]
            );
        }

        if (Str::contains($array["COLUMN_KEY"], "UNI")) 
        {
            $column->unique();
        }

        return $column;
    }

    /**
     * Transforms the Column instance
     * into a SQL representation of
     * that column
     *
     * @return string column's SQL
     */
    public function toSql()
    {
        $sql = Connection::sql()->column(
            $this->name,
            $this->type->sql($this->options),
            $this->nullable,
            $this->default ? $this->type->toDatabaseValue($this->default) : null,
            $this->autoIncrement,
            $this->after
        );

        return $this->resolveConstraints($sql);
    }

    /**
     * Transforms all the possible constraints
     * into SQL
     *
     * @param string $sql
     * @return string column's SQL with its constraints
     */
    protected function resolveConstraints(string $sql)
    {
        $sql = $this->isPrimary($sql);
        $sql = $this->isForeign($sql);
        $sql = $this->isUnique($sql);

        return $sql;
    }

    /**
     * Gets the primary key SQL constraint
     *
     * @param string $sql current column's SQL
     * @return string 
     */
    protected function isPrimary(string $sql)
    {
        if($this->primary) {
            return sprintf(
                "%s, %s",
                $sql,
                Connection::sql()->primaryConstraint($this->name)
            );
        }

        return $sql;
    }

    /**
     * Gets the foreign key SQL constraint
     *
     * @param string $sql current column's SQL
     * @return string 
     */
    protected function isForeign($sql)
    {
        if($this->foreign) {
            return sprintf(
                "%s, %s",
                $sql,
                Connection::sql()->foreignConstraint(
                    $this->table, 
                    $this->name, 
                    $this->foreign["foreign"], 
                    $this->foreign["column"]
                )
            );
        }

        return $sql;
    }

    /**
     * Gets the unique key SQL constraint
     *
     * @param string $sql current column's SQL
     * @return string 
     */
    protected function isUnique($sql)
    {
        if ($this->unique) {
            return sprintf(
                "%s, %s",
                $sql,
                Connection::sql()->uniqueConstraint($this->table, $this->name)
            );
        }

        return $sql;
    }

    /**
     * Gets a SQL for a new column comparing with
     * this current one
     *
     * @param Column $new new Column instance
     * @return string difference SQL
     */
    public function difference(Column $new)
    {
        $newSql = Connection::sql()->column(
            $new->name,
            $new->type->sql($new->options),
            $new->nullable,
            $new->default ? $new->type->toDatabaseValue($new->default) : null,
            $new->autoIncrement,
            $new->after
        );

        
        $newSql = $this->differencePrimary($this, $new, $newSql);
        $newSql = $this->differenceForeign($this, $new, $newSql);
        $newSql = $this->differenceUnique($this, $new, $newSql);

        return $newSql;
    }

    /**
     * Adds or drops the primary constraint in the new
     * column's SQL
     *
     * @param Column $old old Column instance
     * @param Column $new new Column intance
     * @param string $sql column's difference SQL
     * @return string column's difference SQL
     */
    protected function differencePrimary(Column $old, Column $new, string $sql)
    {
        if ($old->primary && !$new->primary) {
            return sprintf(
                "%s, %s",
                $sql,
                Connection::sql()->primaryConstraintDrop()
            );
        } else if (!$old->primary && $new->primary) {
            return sprintf(
                "%s, %s",
                $sql,
                Connection::sql()->primaryConstraintAdd($new->name)
            );
        }

        return $sql;
    }

    /**
     * Adds or drops the foreign constraint in the new
     * column's SQL
     *
     * @param Column $old old Column instance
     * @param Column $new new Column intance
     * @param string $sql column's difference SQL
     * @return string column's difference SQL
     */
    protected function differenceForeign(Column $old, Column $new, string $sql)
    {
        if ($old->foreign && !$new->foreign) {
            return sprintf(
                "%s, %s",
                $sql,
                Connection::sql()->foreignConstraintDrop($this->table, $new->name)
            );
        } else if (!$old->foreign && $new->foreign) {
            return sprintf(
                "%s, %s",
                $sql,
                Connection::sql()->foreignConstraintAdd($this->table, $new->name, $new->foreign["foreign"], $new->foreign["column"])
            );
        }

        return $sql;
    }

    /**
     * Adds or drops the unique constraint in the new
     * column's SQL
     *
     * @param Column $old old Column instance
     * @param Column $new new Column intance
     * @param string $sql column's difference SQL
     * @return string column's difference SQL
     */
    protected function differenceUnique(Column $old, Column $new, string $sql)
    {
        if ($old->unique && !$new->unique) {
            return sprintf(
                "%s, %s",
                $sql,
                Connection::sql()->uniqueConstraintDrop($this->table, $new->name)
            );
        } else if (!$old->unique && $new->unique) {
            $sql = sprintf(
                "%s, %s",
                $sql,
                Connection::sql()->uniqueConstraintAdd($this->table, $new->name)
            );
        }

        return $sql;
    }
}