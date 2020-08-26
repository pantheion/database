<?php

namespace Pantheion\Database\Type;

class Text extends Type
{
    public function sql(array $options = null)
    {
        return Types::TEXT;
    }

    public function toDatabaseValue($var)
    {
        return strval($var);
    }

    public function toCodeValue($var)
    {
        return strval($var);
    }
}
