<?php

namespace Pantheion\Database;

use Pantheion\Database\Dialect\Sql;
use PDO;

class Connection
{
    protected $pdo;
    protected $dialect;

    public function __construct($dsn, $user, $password)
    {
        $this->pdo = new PDO($dsn, $user, $password);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdo->setAttribute(PDO::ATTR_STRINGIFY_FETCHES, false);
    }

    public function execute(string $query, array $params = [])
    {
        $statement = new Statement(
            $this->pdo->prepare($query),
            $params
        );

        return $statement->execute() !== false ? $statement->execute() : intval($this->pdo->lastInsertId());
    }

    public function setDialect(Sql $dialect)
    {
        $this->dialect = $dialect;
        return $this;
    }

    public function sql()
    {
        return $this->dialect;
    }
}