<?php

namespace Pantheion\Database\Type;

/**
 * Represents the Integer Data Type
 */
class Integer extends Type
{
    /**
     * Returns the SQL type
     *
     * @param array $options Optional parameters for the type
     * @return string SQL type
     */
    public function sql($options = null)
    {
        return sprintf(
            "%s %s", 
            Types::INTEGER_TYPE, 
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