<?php

namespace Pantheion\Database\Type;

/**
 * Represents the Varchar Data Type
 */
class Varchar extends Type
{
    /**
     * Minimum length for Varchar type
     */
    const MIN_LENGTH = 0;

    /**
     * Maximum length for Varchar type
     */
    const MAX_LENGTH = 65535;

    /**
     * Returns the SQL type
     *
     * @param array $options Optional parameters for the type
     * @return string SQL type
     */
    public function sql(array $options = null)
    {
        if($options["length"] < Varchar::MIN_LENGTH || $options["length"] > Varchar::MAX_LENGTH) {
            throw new \Exception("VARCHAR bounds exceeded");
        }

        return sprintf(Types::VARCHAR, $options["length"]);
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
