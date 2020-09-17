<?php

namespace Pantheion\Database\Type;

use Pantheion\Database\Dialect\Sql;
use Pantheion\Facade\Connection;

abstract class Type
{
    protected static $types = [];
    protected static $dataTypes = [
        "bigint" => BigInt::class,
        "bit" => Bit::class,
        "char" => Char::class,
        "date" => Date::class,
        "datetime" => DateTime::class,
        "double" => Double::class,
        "float" => FloatColumn::class,
        "int" => Integer::class,
        "integer" => Integer::class,
        "json" => Json::class,
        "longtext" => LongText::class,
        "mediumint" => MediumInt::class,
        "mediumtext" => MediumText::class,
        "smallint" => SmallInt::class,
        "text" => Text::class,
        "time" => Time::class,
        "timestamp" => Timestamp::class,
        "tinyint" => TinyInt::class,
        "tinytext" => TinyText::class,
        "varchar" => Varchar::class
    ];

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

    public static function getClassFromDataType(string $dataType)
    {
        if(!in_array($dataType, array_keys(static::$dataTypes))) {
            throw new \Exception("Incorrect Data Type");
        }

        return static::$dataTypes[$dataType];
    }

    public static function getFromDataType(string $dataType)
    {
        $class = static::getClassFromDataType($dataType);
        return static::get($class);
    }

    protected function wrap($string) 
    {
        return "'".strval($string)."'";
    }
}