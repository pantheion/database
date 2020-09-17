<?php

namespace Pantheion\Database\Type;

/**
 * Represents the SmallInt Data Type
 */
class SmallInt extends Type
{
    /**
     * Returns the SQL type
     *
     * @param array $options Optional parameters for the type
     * @return string SQL type
     */
    public function sql(array $options = null)
    {
        return sprintf(
            "%s %s",
            Types::SMALLINT,
            $options && $options['unsigned'] ? $this->dialect::UNSIGNED : ""
        );
    }

    /**
     * Converts a variable to a Database-ready
     * value
     *
     * @param mixed $var Variable to be converted
     * @return int
     */
    public function toDatabaseValue($var)
    {
        return intval($var);
    }

    public function toCodeValue($var)
    {
        return intval($var);
    }
}
