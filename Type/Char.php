<?php

namespace Pantheion\Database\Type;

/**
 * Represents the Char Data Type
 */
class Char extends Type
{
    /**
     * Minimum length for Char type
     */
    const MIN_LENGTH = 0;

    /**
     * Maximum length for Char type
     */
    const MAX_LENGTH = 255;

    /**
     * Returns the SQL type
     *
     * @param array $options Optional parameters for the type
     * @return string SQL type
     */
    public function sql(array $options = null)
    {
        if ($options["length"] < Varchar::MIN_LENGTH || $options["length"] > Varchar::MAX_LENGTH) {
            throw new \Exception("CHAR bounds exceeded");
        }

        return sprintf(Types::CHAR, $options["length"]);
    }

    /**
     * Converts a variable to a Database-ready
     * value
     *
     * @param mixed $var Variable to be converted
     * @return string Converted value
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
