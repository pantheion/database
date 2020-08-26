<?php

namespace Pantheion\Database\Type;

use Pantheion\Database\Dialect\Sql;
use Pantheion\Facade\Connection;

abstract class Type
{
    protected static $types = [];

    public function __construct(Sql $dialect)
    {
        $this->dialect = $dialect;
    }

    public abstract function sql(array $options = null);
    
    public function toDatabaseValue($value) 
    {
        return $value;
    }
    
    public function toCodeValue($value)
    {
        return $value;
    }

    public static function get(string $typeClass) 
    {
        if(!array_key_exists($typeClass, static::$types)) {
            static::$types[$typeClass] = new $typeClass(Connection::sql());
        }

        return static::$types[$typeClass];
    }
}