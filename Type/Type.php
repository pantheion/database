<?php

namespace Pantheion\Database\Type;

use Pantheion\Database\Dialect\Sql;

abstract class Type
{
    protected static $types = [];

    public abstract static function sql(Sql $dialect);
    
    public static function toDatabaseValue($value) 
    {
        return $value;
    }
    
    public static function toCodeValue($value)
    {
        return $value;
    }

    public static function get(string $typeClass) 
    {
        if(!array_key_exists($typeClass, static::$types)) {
            static::$types[$typeClass] = new $typeClass;
        }

        return static::$types[$typeClass];
    }
}