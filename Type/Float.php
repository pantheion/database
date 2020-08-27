<?php

namespace Pantheion\Database\Type;

class FloatColumn extends Type
{
    public function sql(array $options = null)
    {
        return sprintf(Types::FLOAT_TYPE, $options["length"], $options["precision"]);
    }

    public function toDatabaseValue($var)
    {
        return floatval($var);
    }

    public function toCodeValue($var)
    {
        return floatval($var);
    }
}
