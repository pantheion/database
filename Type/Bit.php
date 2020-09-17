<?php

namespace Pantheion\Database\Type;

/**
 * Represents the Bit Data Type
 */
class Bit extends Type
{
    /**
     * Minimum length for Bit type
     */
    const MIN_LENGTH = 1;

    /**
     * Maximum length for Bit type
     */
    const MAX_LENGTH = 64;

    /**
     * Returns the SQL type
     *
     * @param array $options Optional parameters for the type
     * @return string SQL type
     */
    public function sql(array $options = null)
    {
        if ($options["length"] < Varchar::MIN_LENGTH || $options["length"] > Varchar::MAX_LENGTH) {
            throw new \Exception("BIT bounds exceeded");
        }

        return sprintf(Types::BIT, $options["length"]);
    }

    /**
     * Converts a variable to a Database-ready
     * value
     *
     * @param mixed $var Variable to be converted
     * @return bool Converted value
     */
    public function toDatabaseValue($var)
    {
        return boolval($var);
    }

    public function toCodeValue($var)
    {
        return boolval($var);
    }
}
