<?php

namespace Pantheion\Database\Type;

class MediumInt extends Type
{
    public function sql(array $options = null)
    {
        return sprintf(
            "%s %s",
            Types::MEDIUMINT,
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
