<?php

namespace Pantheion\Database\Table;

use Pantheion\Database\Dialect\Sql;
use Pantheion\Facade\Connection;
use Pantheion\Database\Type\Type;

class Column
{
    public function __construct(string $name, string $type, array $options = null)
    {
        $this->name = $name;
        $this->type = Type::get($type);
        $this->options = $options;
        $this->nullable = null;
        $this->default = null;
        $this->autoIncrement = null;
        $this->after = null;
    }

    public function nullable()
    {
        $this->nullable = true;
        return $this;
    }

    public function default($value)
    {
        $this->default = $value;
        return $this;
    }

    public function autoIncrement()
    {
        $this->autoIncrement = true;
        return $this;
    }

    public function after(string $after)
    {
        $this->after = $after;
        return $this;
    }

    public function toSql(Sql $dialect = null)
    {
        $dialect = $dialect ?: Connection::sql();

        $sql = $dialect->column(
            $this->name,
            $this->type->sql($this->options),
            $this->nullable ?: "",
            $this->default ? $this->type->toDatabaseValue($this->default) : "",
            $this->autoIncrement ?: "",
            $this->after ?: ""
        );

        dd($sql);
        return $sql;
    }
}