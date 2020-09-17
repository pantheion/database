<?php

namespace Pantheion\Database\Type;

/**
 * Represents the Double Data Type
 */
class Double extends Type
{
    /**
     * Returns the SQL type
     *
     * @param array $options Optional parameters for the type
     * @return string SQL type
     */
    public function sql(array $options = null)
    {
        return sprintf(Types::DOUBLE_TYPE, $options["length"], $options["precision"]);
    }

    /**
     * Converts a variable to a Database-ready
     * value
     *
     * @param mixed $var Variable to be converted
     * @return double
     */
    public function toDatabaseValue($var)
    {
        return doubleval($var);
    }

    public function toCodeValue($var)
    {
        return doubleval($var);
    }
}
