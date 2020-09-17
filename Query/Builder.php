<?php

namespace Pantheion\Database\Query;

use Pantheion\Facade\Arr;
use Pantheion\Facade\Connection;

class Builder
{
    protected $connection;
    protected $table;

    public function __construct(string $table)
    {      
        $this->table = $table;
    }

    public function select(...$columns)
    {

    }

    public function insert(array $insert)
    {
        if(!Arr::isAssoc($insert)) {
            throw new \Exception("Please provide an associative array with the columns and their values");
        }

        $columns = array_keys($insert);
        $sql = Connection::sql()->insert($this->table, $columns);
    }

    public function get()
    {
        
    }
}
