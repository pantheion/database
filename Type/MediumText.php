<?php

namespace Pantheion\Database\Type;

class MediumText extends Type
{
    public function sql(array $options = null)
    {
        return Types::MEDIUMTEXT;
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
