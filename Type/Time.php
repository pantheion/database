<?php

namespace Pantheion\Database\Type;

class Time extends Type
{
    public function sql(array $options = null)
    {
        return Types::TIME;
    }

    public function toDatabaseValue($var)
    {
        return $var instanceof \DateTime ? $var->format("H:i:s") : $var;
    }

    public function toCodeValue($var)
    {
        return \DateTime::createFromFormat("H:i:s", $var);
    }
}
