<?php

namespace Pantheion\Database\Type;

class Char extends Type
{
    const MIN_LENGTH = 0;
    const MAX_LENGTH = 255;

    public function sql(array $options = null)
    {
        if ($options["length"] < Varchar::MIN_LENGTH || $options["length"] > Varchar::MAX_LENGTH) {
            throw new \Exception("CHAR bounds exceeded");
        }

        return sprintf(Types::CHAR, $options["length"]);
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
