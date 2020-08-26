<?php

namespace Pantheion\Database\Table;

use Pantheion\Database\Connection;
use Pantheion\Facade\Inflection;

class Manager
{
    public function create(string $table, \Closure $schematic)
    {
        $table = Inflection::tablerize($table);

        $schema = new Schema;

        
        $schematic($schema);
        // dd($schema);
        dd($schema->toSql());
    }

    public function exists(string $table)
    {

    }

    public function use(string $table)
    {
        
    }

    public function query(string $table)
    {
        
    }
}