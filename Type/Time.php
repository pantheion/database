<?php

namespace Pantheion\Database\Type;

use Carbon\Carbon;

/**
 * Represents the Time Data Type
 */
class Time extends Type
{
    /**
     * Returns the SQL type
     *
     * @param array $options Optional parameters for the type
     * @return string SQL type
     */
    public function sql(array $options = null)
    {
        return Types::TIME;
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
        return $var instanceof \DateTime ? $var->format("H:i:s") : $var;
    }

    public function toCodeValue($var)
    {
        return Carbon::createFromFormat("H:i:s", $var);
    }
}
