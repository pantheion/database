<?php

namespace Pantheion\Database\Table;

use Pantheion\Database\Connection;

class Manager
{
    public function __construct()
    {
        $this->m = new Manager("mysql");
        $this->c = $this->m->connect([
            'host' => 'localhost',
            'port' => '3306',
            'database' => 'zephyr',
            'user' => 'root',
            'password' => '',
        ]);
    }

    public function create(string $table, Schema $schema)
    {
        
    }

    public function exists(string $table)
    {

    }

    public function query(string $table)
    {
        
    }
}