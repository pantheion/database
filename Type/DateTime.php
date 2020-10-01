<?php

namespace Pantheion\Database\Type;

use Carbon\Carbon;

/**
 * Represents the DateTime Data Type
 */
class DateTime extends Type
{
    /**
     * Returns the SQL type
     *
     * @param array $options Optional parameters for the type
     * @return string SQL type
     */
    public function sql(array $options = null)
    {
        return Types::DATETIME;
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
        return $var instanceof \DateTime ? $var->format("Y-m-d H:i:s") : $var;
    }

    public function toCodeValue($var)
    {
        return Carbon::createFromFormat("Y-m-d H:i:s", $var);
    }
}
