<?php

namespace Pantheion\Database;

use Pantheion\Database\Dialect\Sql;
use PDO;

/**
 * Class that represents a database connection
 * with its functionalities to execute SQL queries.
 */
class Connection
{
    /**
     * PDO instance
     *
     * @var \PDO
     */
    protected $pdo;

    /**
     * Sql dialect instance based on
     * the database driver
     *
     * @var \Pantheion\Database\Dialect\Sql
     */
    protected $dialect;

    /**
     * Constructor function for Connection
     *
     * @param string $dsn Connection String
     * @param string $user User name
     * @param string $password User's password
     */
    public function __construct(string $dsn, string $user, string $password)
    {
        $this->pdo = new PDO($dsn, $user, $password);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdo->setAttribute(PDO::ATTR_STRINGIFY_FETCHES, false);
    }

    /**
     * Executes a SQL query, that can be perfomed
     * with positional parameters
     *
     * @param string $query Raw SQL query
     * @param array $params Array of positional parameters
     * @return mixed Result from the statement's execution
     */
    public function execute(string $query, array $params = [])
    {
        $statement = new Statement(
            $this->pdo->prepare($query),
            $params
        );

        $result = $statement->execute();

        if($result === false) {
            return intval($this->pdo->lastInsertId());
        }

        return $result;
    }

    /**
     * Sets the current connection's dialect
     *
     * @param \Pantheion\Database\Dialect\Sql $dialect SQL dialect instance
     * @return Connection Current instance
     */
    public function setDialect(Sql $dialect)
    {
        $this->dialect = $dialect;
        return $this;
    }

    /**
     * Returns the SQL Dialect instance
     *
     * @return \Pantheion\Database\Dialect\Sql Dialect instance for this Connection
     */
    public function sql()
    {
        return $this->dialect;
    }
}