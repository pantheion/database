<?php

namespace Pantheion\Database\Type;

class TinyInt extends Type
{
    public function sql(array $options = null)
    {
        return sprintf(
            "%s %s",
            Types::TINYINT,
            $options && $options['unsigned'] ? $this->dialect::UNSIGNED : ""
        );
    }

    public function toDatabaseValue($var)
    {
        return intval($var);
    }

    public function toCodeValue($var)
    {
        return intval($var);
    }
}
