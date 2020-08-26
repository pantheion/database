<?php

namespace Pantheion\Database\Type;

class LongText extends Type
{
    public function sql(array $options = null)
    {
        return Types::LONGTEXT;
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
