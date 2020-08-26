<?php

namespace Pantheion\Database\Dialect;

class Sql
{
    const SELECT = "SELECT %s %s FROM %s %s %s %s %s %s %s";

    const CREATE_TABLE = "CREATE TABLE `%s` (%s) DEFAULT CHARACTER SET=utf8mb4;";
    const DROP_TABLE = "DROP TABLE `%s`";

    const COLUMN = "%s %s %s %s %s %s";
    const UNSIGNED = "UNSIGNED";

    protected function _($format, ...$args)
    {
        return str_replace("  ", " ", trim(vsprintf($format, $args)));
    }

    public function column(
        string $name, 
        string $type,
        bool $nullable = null, 
        $default = null, 
        bool $autoIncrement = null,
        string $after = null)
    {
        dd($type);   
        // return $this->_(
        //     static::COLUMN,
        //     $name,
        //     $type,

        // );
    }
}
