<?php

namespace Pantheion\Database\Type;

class Double extends Type
{
    public function sql(array $options = null)
    {
        return sprintf(Types::DOUBLE_TYPE, $options["length"], $options["precision"]);
    }

    public function toDatabaseValue($var)
    {
        return doubleval($var);
    }

    public function toCodeValue($var)
    {
        return doubleval($var);
    }
}
