<?php

namespace Pantheion\Database\Type;

class Bit extends Type
{
    const MIN_LENGTH = 1;
    const MAX_LENGTH = 64;

    public function sql(array $options = null)
    {
        if ($options["length"] < Varchar::MIN_LENGTH || $options["length"] > Varchar::MAX_LENGTH) {
            throw new \Exception("BIT bounds exceeded");
        }

        return sprintf(Types::BIT, $options["length"]);
    }

    public function toDatabaseValue($var)
    {
        return boolval($var);
    }

    public function toCodeValue($var)
    {
        return boolval($var);
    }
}
