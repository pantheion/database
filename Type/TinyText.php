<?php

namespace Pantheion\Database\Type;

/**
 * Represents the TintText Data Type
 */
class TinyText extends Type
{
    /**
     * Returns the SQL type
     *
     * @param array $options Optional parameters for the type
     * @return string SQL type
     */
    public function sql(array $options = null)
    {
        return Types::TINYTEXT;
    }

    /**
     * Converts a variable to a Database-ready
     * value
     *
     * @param mixed $var Variable to be converted
     * @return string
     */
    public function toDatabaseValue($var)
    {
        return $this->wrap($var);
    }

    public function toCodeValue($var)
    {
        return strval($var);
    }
}
