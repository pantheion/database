<?php

namespace Pantheion\Database\Dialect;

class Sql
{
    protected const CREATE_TABLE = "CREATE TABLE `%s` (%s) DEFAULT CHARACTER SET=utf8mb4;";
    protected const DROP_TABLE = "DROP TABLE `%s`";

    protected function _($format, ...$args)
    {
        return str_replace("  ", " ", trim(vsprintf($format, $args)));
    }

    public function createTable(string $table, string $columns)
    {
        return $this->_(Sql::CREATE_TABLE, $table, $columns);
    }

    public function dropTable(string $table)
    {
        return $this->_(Sql::DROP_TABLE, $table);
    }
}
