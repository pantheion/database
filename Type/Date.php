<?php

namespace Pantheion\Database\Type;

class Date extends Type
{
    public function sql(array $options = null)
    {
        return Types::DATE;
    }

    public function toDatabaseValue($var)
    {
        return $var instanceof \DateTime ? $var->format("Y-m-d") : $var;
    }

    public function toCodeValue($var)
    {
        return \DateTime::createFromFormat("Y-m-d", $var);
    }
}
