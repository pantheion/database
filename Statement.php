<?php

namespace Pantheion\Database;

use Pantheion\Database\Type\DateTime;
use Pantheion\Database\Type\Type;
use Pantheion\Facade\Arr;
use Pantheion\Facade\Str;
use PDOStatement;
use PDO;

class Statement
{
    protected $pdoStatement;
    protected $params;

    public function __construct(PDOStatement $pdoStatement, array $params = [])
    {
        $this->pdoStatement = $pdoStatement;
        $this->params = $params;
    }

    public function execute()
    {
        if(!Arr::empty($this->params)) {
            foreach($this->params as $i => $param) {
                $this->bind($param, $i);
            }
        }

        return $this->resolve();
    }

    protected function bind($param, int $i)
    {
        $type = gettype($param);
    
        if (Str::contains($type, "integer")) {
            return $this->pdoStatement->bindValue($i + 1, $param, PDO::PARAM_INT);
        }

        if (Str::contains($type, "boolean")) {
            return $this->pdoStatement->bindValue($i + 1, $param, PDO::PARAM_BOOL);
        }

        if ($param instanceof \DateTime) {
            $value = Type::get(DateTime::class)->toDatabaseValue($param);
            return $this->pdoStatement->bindValue($i + 1, $value, PDO::PARAM_STR);
        }

        return $this->pdoStatement->bindValue($i + 1, $param, PDO::PARAM_STR);
    }

    protected function resolve()
    {
        try {
            $this->pdoStatement->execute();
        }
        catch(\Exception $e) {
            trigger_error("The query '{$this->pdoStatement->queryString}' failed while executing. Message: {$e}");
        }

        if(Str::contains($this->pdoStatement->queryString, "INSERT INTO")) {
            return false;
        }


        return $this->pdoStatement->rowCount() > 0 ? $this->pdoStatement->fetchAll(PDO::FETCH_ASSOC) : true;
    }
}