<?php

namespace Pantheion\Database\Dialect;

use Pantheion\Facade\Connection;

/**
 * Library with raw SQL statements
 */
class Sql
{
    /**
     * SELECT Clauses
     */
    // [columns] [distinct] [table] [group by] [having] [order by] [limit and offset]
    const SELECT = "SELECT %s %s FROM %s %s %s %s %s %s";
    const DISTINCT = "DISTINCT";
    const WHERE = "WHERE %s";
    const WHERE_AND = "AND %s";
    const WHERE_OR = "OR %s";
    const WHERE_BETWEEN = "BETWEEN ? AND ?";
    const WHERE_NOT_BETWEEN = "NOT BETWEEN ? AND ?";
    const WHERE_IN = "IN %s";
    const WHERE_NOT_IN = "NOT IN %s";
    const WHERE_NULL = "IS NULL";
    const WHERE_NOT_NULL = "IS NOT NULL";
    const GROUP_BY = "GROUP BY %s";
    const HAVING = "HAVING %s";
    const ORDER_BY = "ORDER BY %s";
    const LIMIT = "LIMIT %s";
    const LIMIT_MAX = "18446744073709551615";
    const OFFSET = "OFFSET %s";

    /**
     * Tables
     */
    const CREATE_TABLE = "CREATE TABLE `%s` (%s) DEFAULT CHARACTER SET %s COLLATE %s COMMENT '%s'";
    const ALTER_TABLE = "ALTER TABLE `%s` %s";
    const ADD_COLUMN = "ADD COLUMN %s";
    const MODIFY_COLUMN = "MODIFY COLUMN %s";
    const RENAME_COLUMN = "CHANGE `%s` %s";
    const DROP_COLUMN = "DROP COLUMN `%s`";
    const DROP_TABLE = "DROP TABLE `%s`";
    const TRUNCATE_TABLE = "TRUNCATE TABLE `%s`";

    /**
     * Table Relations
     */
    const BELONGS_TO = "SELECT TABLE_NAME, COLUMN_NAME, 
                        REFERENCED_TABLE_NAME AS FOREIGN_TABLE_NAME, 
                        REFERENCED_COLUMN_NAME AS FOREIGN_COLUMN_NAME
                        FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
                        WHERE TABLE_SCHEMA = '%s' AND TABLE_NAME='%s' AND REFERENCED_TABLE_NAME IS NOT NULL";
    const HAS_MANY = "SELECT TABLE_NAME, COLUMN_NAME
                      FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
                      WHERE TABLE_SCHEMA = '%s' AND REFERENCED_TABLE_NAME = '%s'";

    /**
     * Primary Key Constraints
     */
    const PRIMARY_KEY_CT= "CONSTRAINT `pk` PRIMARY KEY (`%s`)";
    const PRIMARY_KEY_CT_ADD = "ADD CONSTRAINT `pk` PRIMARY KEY (`%s`)";
    const PRIMARY_KEY_CT_DROP = "DROP PRIMARY KEY";

    /**
     * Foreign Key Constraints
     */
    const FOREIGN_KEY_CT = "CONSTRAINT %s_%s_foreign FOREIGN KEY (`%s`) REFERENCES `%s` (`%s`)";
    const FOREIGN_KEY_CT_NAME = "SELECT * 
                                FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
                                WHERE CONSTRAINT_NAME LIKE '%%foreign%%' 
                                AND TABLE_SCHEMA = '%s'
                                AND TABLE_NAME = '%s' 
                                AND COLUMN_NAME = '%s'";
    const FOREIGN_KEY_CT_ADD = "ADD CONSTRAINT %s_%s_foreign FOREIGN KEY (`%s`) REFERENCES `%s` (`%s`)";
    const FOREIGN_KEY_CT_DROP = "DROP FOREIGN KEY `%s`, DROP INDEX  `%s`";

    /**
     * Unique Constraints
     */
    const UNIQUE_CT = "CONSTRAINT %s_%s_unique UNIQUE (`%s`)";
    const UNIQUE_CT_NAME = "SELECT * 
                            FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
                            WHERE CONSTRAINT_NAME LIKE '%%unique%%'
                            AND TABLE_SCHEMA = '%s'
                            AND TABLE_NAME = '%s'
                            AND COLUMN_NAME = '%s'";
    const UNIQUE_CT_ADD = "ADD CONSTRAINT %s_%s_unique UNIQUE (`%s`)";
    const UNIQUE_CT_DROP = "DROP INDEX %s";

    /**
     * Data Clauses
     */
    const INSERT = "INSERT INTO `%s` (%s) VALUES %s";
    const UPDATE = "UPDATE `%s` SET %s %s";
    const DELETE = "DELETE FROM `%s` %s";

    /**
     * Column Clauses
     */
    const COLUMN = "`%s` %s %s %s %s %s";
    const UNSIGNED = "UNSIGNED";
    const NULL_VALUE = "NULL";
    const NOT_NULL = "NOT NULL";
    const DEFAULT = "DEFAULT %s";
    const AFTER = "AFTER `%s`";
    const AUTO_INCREMENT = "AUTO_INCREMENT";

    /**
     * Reflections
     */
    const COLUMNS = "SELECT * 
                    FROM INFORMATION_SCHEMA.COLUMNS
    				WHERE TABLE_SCHEMA = '%s' AND TABLE_NAME = '%s'";
    const TABLES = "SELECT * 
                    FROM INFORMATION_SCHEMA.TABLES 
                    WHERE TABLE_SCHEMA = '%s'";
    const TABLE = "SELECT *
                    FROM `INFORMATION_SCHEMA`.`TABLES` AS `T`,
                    `INFORMATION_SCHEMA`.`COLLATION_CHARACTER_SET_APPLICABILITY` AS `CCSA`
                    WHERE `CCSA`.`COLLATION_NAME` = `T`.`TABLE_COLLATION`
                    AND `T`.`TABLE_SCHEMA` = '%s'
                    AND `T`.`TABLE_NAME` = '%s'";
    CONST TABLE_EXISTS = "SELECT COUNT(*) AS count
                        FROM INFORMATION_SCHEMA.TABLES 
                        WHERE TABLE_SCHEMA = '%s' AND TABLE_NAME = '%s'
                        LIMIT 1";

    /**
     * Trims both sides of a string and
     * substitutes the placeholders from
     * that string
     *
     * @param string $format template of the string
     * @param ...$args parameters to replace
     * @return string formated string with the parameters in place
     */
    protected function _(string $format, ...$args)
    {
        return str_replace("  ", " ", trim(vsprintf($format, $args)));
    }

    /**
     * Adds columns to a table
     *
     * @param string $name table's name
     * @param string $columns columns' definition
     * @return string
     */
    public function addColumns(string $name, string $columns)
    {
        return $this->_(
            static::ALTER_TABLE,
            $name,
            $columns
        );
    }

    /**
     * Renames a column from a table
     *
     * @param string $name table's name
     * @param string $from old name
     * @param string $to new name
     * @return string
     */
    public function renameColumn(string $name, string $from, string $to)
    {
        return $this->_(
            static::ALTER_TABLE,
            $name,
            $this->_(
                static::RENAME_COLUMN,
                $from,
                $to
            )
        );
    }

    /**
     * Raw SQL query to modify a column from a table.
     *
     * @param string $name table's name
     * @param string $columns columns' definition to modify
     * @return string
     */
    public function alterColumns(string $name, string $columns)
    {
        return $this->_(
            static::ALTER_TABLE,
            $name,
            $columns
        );
    }

    /**
     * Raw SQL query to drop a column from a table.
     * It needs to be concatenated with and ALTER TABLE
     * statement
     *
     * @param string $name column's name
     * @return string
     */
    public function dropColumn(string $name)
    {
        return $this->_(
            static::DROP_COLUMN,
            $name
        );
    }

    /**
     * Builds a raw SQL representation
     * of a column
     *
     * @param string $name column's name
     * @param string $type column's type
     * @param boolean $nullable whether the column is nullable or not
     * @param mixed $default column's default value
     * @param boolean $autoIncrement whether the column auto increments
     * @param string $after column to place the column after
     * @return string
     */
    public function column(
        string $name, 
        string $type,
        bool $nullable = null, 
        $default = null, 
        bool $autoIncrement = null,
        string $after = null)
    {
        $notNull = true;

        if($nullable && is_null($default)) {
            $notNull = false;
            $default = static::NULL_VALUE;
        } else if($nullable && !is_null($default)) {
            $notNull = false;
        }

        return $this->_(
            static::COLUMN,
            $name,
            $type,
            $notNull ? "NOT NULL" : "",
            $default ? $this->_(static::DEFAULT, $default) : "",
            $autoIncrement ? static::AUTO_INCREMENT : "",
            $after ? $this->_(static::AFTER, $after) : ""
        );
    }

    /**
     * Sets an PRIMARY constraint during the
     * creation of a table
     *
     * @param string $column constraint's column
     * @return string
     */
    public function primaryConstraint(string $column)
    {
        return $this->_(
            static::PRIMARY_KEY_CT,
            $column
        );
    }

    /**
     * Raw SQL query to add an FOREIGN constraint
     *
     * @param string $column
     * @return string
     */
    public function primaryConstraintAdd(string $column)
    {
        return $this->_(
            static::PRIMARY_KEY_CT_ADD,
            $column
        );
    }

    /**
     * Raw SQL query to drop a PRIMARY constraint
     *
     * @return string
     */
    public function primaryConstraintDrop()
    {
        return static::PRIMARY_KEY_CT_DROP;
    }

    /**
     * Sets an FOREIGN constraint during the
     * creation of a table
     *
     * @param string $table table's name
     * @param string $column constraint's column
     * @param string $foreign foreign table
     * @param string $foreignColumn foreign table's column
     * @return string
     */
    public function foreignConstraint(string $table, string $column, string $foreign, string $foreignColumn)
    {
        return $this->_(
            static::FOREIGN_KEY_CT,
            $table,
            $column,
            $column,
            $foreign,
            $foreignColumn
        );
    }

    /**
     * Raw SQL query to get an FOREIGN constraint's name
     *
     * @param string $table table's name
     * @param string $column constraint's column
     * @return string
     */
    public function foreignConstraintName(string $table, string $column)
    {
        return $this->_(
            static::FOREIGN_KEY_CT_NAME,
            'zephyr',
            $table,
            $column
        );
    }

    /**
     * Raw SQL query to add an FOREIGN constraint
     *
     * @param string $table table's name
     * @param string $column constraint's column
     * @param string $foreign foreign table
     * @param string $foreignColumn foreign table's column
     * @return string
     */
    public function foreignConstraintAdd(string $table, string $column, string $foreign, string $foreignColumn)
    {
        return $this->_(
            static::FOREIGN_KEY_CT_ADD,
            $table,
            $column,
            $column,
            $foreign,
            $foreignColumn
        );
    }

    /**
     * Raw SQL query to drop a FOREIGN constraint
     *
     * @param string $table table's name
     * @param string $column constraint's column
     * @return string
     */
    public function foreignConstraintDrop(string $table, string $column)
    {
        $constraint = Connection::execute(
            $this->foreignConstraintName($table, $column)
        )[0];
      
        return $this->_(
            static::FOREIGN_KEY_CT_DROP,
            $constraint["CONSTRAINT_NAME"],
            $constraint["CONSTRAINT_NAME"]
        );
    }

    /**
     * Sets an UNIQUE constraint during the
     * creation of a table
     *
     * @param string $table table's name
     * @param string $column constraint's column
     * @return string
     */
    public function uniqueConstraint(string $table, string $column)
    {
        return $this->_(
            static::UNIQUE_CT,
            $table,
            $column,
            $column
        );
    }

    /**
     * Raw SQL query to add an UNIQUE constraint
     *
     * @param string $table table's name
     * @param string $column constraint's column
     * @return string
     */
    public function uniqueConstraintAdd(string $table, string $column)
    {
        return $this->_(
            static::UNIQUE_CT_ADD,
            $table,
            $column,
            $column
        );
    }

    /**
     * Raw SQL query to get an UNIQUE constraint's name
     *
     * @param string $table table's name
     * @param string $column constraint's column
     * @return string
     */
    public function uniqueConstraintName(string $table, string $column)
    {
        return $this->_(
            static::UNIQUE_CT_NAME,
            'zephyr',
            $table,
            $column
        );
    }

    /**
     * Raw SQL query to drop a UNIQUE constraint
     *
     * @param string $table table's name
     * @param string $column constraint's column
     * @return string
     */
    public function uniqueConstraintDrop(string $table, string $column)
    {
        $constraint = Connection::execute(
            $this->uniqueConstraintName($table, $column)
        )[0];

        return $this->_(
            static::UNIQUE_CT_DROP,
            $constraint["CONSTRAINT_NAME"]
        );
    }

    /**
     * Raw SQL query that lists all the
     * tables in the database
     *
     * @return string
     */
    public function tables()
    {
        return $this->_(
            static::TABLES,
            'zephyr'
        );
    }

    /**
     * Raw SQL to retrieve the metadata
     * from a certain table
     *
     * @param string $table table's name
     * @return string
     */
    public function table(string $table)
    {
        return $this->_(
            static::TABLE,
            'zephyr',
            $table
        );
    }

    /**
     * Raw SQL query with a list of
     * columns from a table
     *
     * @param string $table table's name
     * @return void
     */
    public function columns(string $table)
    {
        return $this->_(
            static::COLUMNS,
            'zephyr',
            $table
        );
    }

    /**
     * Raw SQL query to check if a table
     * exists or not
     *
     * @param string $table table's name
     * @return string
     */
    public function exists(string $table)
    {
        return $this->_(
            static::TABLE_EXISTS,
            'zephyr',
            $table
        );
    }

    /**
     * Raw SQL of a list of tables
     * that the current table has an
     * BelongsTo relation
     *
     * @param string $table
     * @return string
     */
    public function belongsTo(string $table)
    {
        return $this->_(
            static::BELONGS_TO,
            'zephyr',
            $table
        );
    }

    /**
     * Raw SQL of a list of tables
     * that the current table has an
     * HasMany relation
     *
     * @param string $table
     * @return string
     */
    public function hasMany(string $table)
    {
        return $this->_(
            static::HAS_MANY,
            'zephyr',
            $table
        );
    }

    /**
     * Raw SQL of a list of tables
     * that the current table has an
     * BelongsToMany relation
     *
     * @param string $table
     * @return string
     */
    public function belongsToMany(string $table)
    {
    }

    /**
     * Builds an INSERT INTO statement
     * 
     * @param string $table table's name
     * @param array $columns names of the columns to perform the insert action
     * @param int $times number of insert actions to perform
     * @return string
     */
    public function insert(string $table, array $columns, int $times = 1)
    {
        $columnsString = join(', ', array_map(function($column) {
            return "`".$column."`";
        }, $columns));

        for($i = 0; $i < $times; $i++) {
            $questionMarks[] = "(" . join(", ", array_pad([], count($columns), "?")) . ")";
        }
        
        $questionMarks = join(", ", $questionMarks);

        return $this->_(
            static::INSERT,
            $table,
            $columnsString,
            $questionMarks
        );
    }

    /**
     * Builds an UPDATE string
     *
     * @param string $table table's name
     * @param array $columns names of the columns to perform the update action
     * @param string $where where clause SQL string
     * @return string
     */
    public function update(string $table, array $columns, string $where)
    {
        $updateColumns = join(", ", array_map(function ($key, $questionMark) {
            return "`" . $key . "` = " . $questionMark;
        }, $columns, array_pad([], count($columns), '?')));
        
        return $this->_(
            static::UPDATE,
            $table,
            $updateColumns,
            $where
        );
    }

    /**
     * Builds a DELETE string
     *
     * @param string $table table's name
     * @param string $where where clause SQL string
     * @return string
     */
    public function delete(string $table, string $where)
    {
        return $this->_(
            static::DELETE,
            $table,
            $where
        );
    }
}
