<?php

namespace Pantheion\Database;

use Pantheion\Database\Dialect\MySql;
use Pantheion\Database\Dialect\Postgres;
use Pantheion\Database\Dialect\SQLite;
use Pantheion\Database\Dialect\SqlServer;

class Manager
{
    const DRIVERS = [
        'mysql', 'sqlsrv', 'pgsql', 'sqlite'
    ];

    const CONN_STRINGS = [
        "mysql" => "mysql:host=%s:%s;dbname=%s;charset=utf8mb4",
        "sqlsrv" => "sqlsrv:Server=%s,%s;Database=%s",
        "pgsql" => "pgsql:host=%s;port=%s;dbname=%s",
        "sqlite" => "sqlite:%s"
    ];

    const SQL_DIALECTS = [
        "mysql" => MySql::class,
        "sqlsrv" => SqlServer::class,
        "pgsql" => Postgres::class,
        "sqlite" => SQLite::class
    ];

    protected $connection = null;
    protected $driver;

    public function __construct(string $driver)
    {
        if (!$this->isValidDriver($driver)) {
            throw new \Exception("'{$driver}' is not a valid connection driver.");
        }

        $this->driver = $driver;
    }

    public function connect(array $options = null) 
    {
        if(isset($this->connection)) {
            return $this->connection;
        }

        $this->validate($options);
        return $this->connection = $this->getConnection($options);
    }

    protected function validate(array $options) 
    {
        if(!array_key_exists('host', $options)) {
            throw new \Exception("Missing 'host' in the connection parameters.");
        }

        if (!array_key_exists('port', $options)) {
            throw new \Exception("Missing 'port' in the connection parameters.");
        }

        if (!array_key_exists('database', $options)) {
            throw new \Exception("Missing 'database' in the connection parameters.");
        }

        if (!array_key_exists('user', $options)) {
            throw new \Exception("Missing 'user' in the connection parameters.");
        }

        if (!array_key_exists('password', $options)) {
            throw new \Exception("Missing 'password' in the connection parameters.");
        }
    }

    protected function isValidDriver(string $driver)
    {
        return in_array($driver, Manager::DRIVERS);
    }

    protected function getConnection(array $options)
    {
        return (new Connection(
            $this->getConnectionString($options), 
            $options['user'], 
            $options['password']
        ))->setDialect($this->getDialect());
    }

    protected function getConnectionString(array $options) 
    {
        $format = Manager::CONN_STRINGS[$this->driver];
        
        if($this->driver === 'sqlite') {
            return sprintf($format, $options['file']);
        }

        return sprintf(
            $format,
            $options['host'], $options['port'], $options['database']
        );
    }

    protected function getDialect()
    {
        if(!in_array($this->driver, array_keys(Manager::SQL_DIALECTS))) {
            throw new \Exception("'{$this->driver}' is not a valid SQL dialect.");
        }

        $dialect = Manager::SQL_DIALECTS[$this->driver];
        return new $dialect;
    }
}