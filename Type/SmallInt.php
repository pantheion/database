<?php

namespace Pantheion\Database\Type;

class SmallInt extends Type
{
    public function sql(array $options = null)
    {
        return sprintf(
            "%s %s",
            Types::SMALLINT,
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
