<?php

namespace Pantheion\Database\Table;

use Pantheion\Facade\Inflection;

class Table
{
    protected const MODEL_NAMESPACE = "App\\Model\\";

    public function __construct(string $name)
    {
        $this->name = $name;
        $this->model = Inflection::classerize($name);
        $this->class = Table::MODEL_NAMESPACE . $this->model;
    }
}