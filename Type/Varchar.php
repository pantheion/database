<?php

namespace Pantheion\Database\Type;

use Pantheion\Database\Dialect\Sql;

class Varchar extends Type
{
    const MIN_LENGTH = 0;
    const MAX_LENGTH = 65535;

    public function sql(array $options = null)
    {
        if($options["length"] < Varchar::MIN_LENGTH || $options["length"] > Varchar::MAX_LENGTH) {
            throw new \Exception("VARCHAR bounds exceeded");
        }

        return sprintf(Types::VARCHAR, $options["length"]);
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
