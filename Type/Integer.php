<?php

namespace Pantheion\Database\Type;

use Pantheion\Database\Dialect\Sql;

class Integer extends Type
{
    public function sql($options = null)
    {
        return sprintf(
            "%s %s", 
            Types::INTEGER_TYPE, 
            $options && $options['unsigned'] ? $this->dialect::UNSIGNED : ""
        );
    }

    public function toDatabaseValue($var)
    {
        return strval($var);
    }

    public function toCodeValue($var)
    {
        return intval($var);
    }
}