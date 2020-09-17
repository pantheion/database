<?php

namespace Pantheion\Database\Type;

/**
 * Represents the Float Data Type
 */
class FloatColumn extends Type
{
    /**
     * Returns the SQL type
     *
     * @param array $options Optional parameters for the type
     * @return string SQL type
     */
    public function sql(array $options = null)
    {
        return sprintf(Types::FLOAT_TYPE, $options["length"], $options["precision"]);
    }

    /**
     * Converts a variable to a Database-ready
     * value
     *
     * @param mixed $var Variable to be converted
     * @return float
     */
    public function toDatabaseValue($var)
    {
        return floatval($var);
    }

    public function toCodeValue($var)
    {
        return floatval($var);
    }
}
