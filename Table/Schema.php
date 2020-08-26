<?php

namespace Pantheion\Database\Table;

use Pantheion\Facade\Connection;
use Pantheion\Database\Dialect\Sql;
use Pantheion\Database\Type\Integer;
use Pantheion\Database\Type\Varchar;

class Schema
{
    protected $charset;
    protected $collation;
    protected $columns;

    public function __construct()
    {
        $this->columns = [];    
    }

    protected function add(Column $column)
    {
        $this->columns[] = $column;
        return $column;
    }

    public function integer(string $name)
    {
        return $this->add(new Column($name, Integer::class));
    }

    public function unsignedInt(string $name)
    {
        return $this->add(new Column($name, Integer::class, ['unsigned' => true]));
    }

    public function varchar(string $name, int $length = 255)
    {
        return $this->add(new Column($name, Varchar::class, compact('length')));
    }

    public function toSql(Sql $dialect = null)
    {
        $dialect = isset($dialect) ?: Connection::sql();
        
        $columnsSql = [];
        foreach($this->columns as $column) {
            $columnsSql[] = $column->toSql($dialect);
        }
        
        dd($columnsSql);
        // dd($dialect::SELECT);
    }
}