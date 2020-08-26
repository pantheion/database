<?php

namespace Pantheion\Database\Type;

use Pantheion\Database\Dialect\MySql;
use Pantheion\Database\Dialect\Sql;

class DateTime extends Type
{
    public static function sql(string $column, Sql $sql)
    {
    }

    public static function toDatabaseValue($var)
    {
        return $var->format("Y-m-d H:i:s");
    }

    public static function toCodeValue($var)
    {
    }
}
