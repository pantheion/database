<?php

namespace Pantheion\Database\Type;

class TinyText extends Type
{
    public function sql(array $options = null)
    {
        return Types::TINYTEXT;
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
