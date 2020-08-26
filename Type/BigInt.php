<?php

namespace Pantheion\Database\Type;

class BigInt extends Type
{
    public function sql(array $options = null)
    {
        return sprintf(
            "%s %s",
            Types::BIGINT,
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
