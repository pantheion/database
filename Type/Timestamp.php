<?php

namespace Pantheion\Database\Type;

class Timestamp extends Type
{
    public function sql(array $options = null)
    {
        return Types::TIMESTAMP;
    }

    public function toDatabaseValue($var)
    {
        return $var instanceof \DateTime ? $var->format("Y-m-d H:i:s") : $var;
    }

    public function toCodeValue($var)
    {
        return \DateTime::createFromFormat("Y-m-d H:i:s", $var);
    }
}
