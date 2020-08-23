<?php

namespace Pantheion\Database\Query;

use Pantheion\Database\Connection;

class Builder
{
    protected $connection;
    protected $table;

    public function __construct(Connection $connection, string $table)
    {      
        $this->connection = $connection;
        $this->table = $table;
    }

    public function select(...$columns)
    {

    }

    public function get()
    {
        
    }
}
