<?php

namespace Pantheion\Database\Type;

class Json extends Type
{
    public function sql(array $options = null)
    {
        return Types::JSON;
    }

    public function toDatabaseValue($var)
    {
        return json_encode($var);
    }

    public function toCodeValue($var)
    {
        return json_decode($var, true);
    }
}
