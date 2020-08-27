<?php

namespace Pantheion\Database\Type;

class Integer extends Type
{
    public function sql($options = null)
    {
        return sprintf(
            "%s %s", 
            Types::INTEGER_TYPE, 
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