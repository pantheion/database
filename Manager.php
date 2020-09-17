<?php

namespace Pantheion\Database;

use Pantheion\Database\Dialect\MySql;
use Pantheion\Database\Dialect\Postgres;
use Pantheion\Database\Dialect\SQLite;
use Pantheion\Database\Dialect\SqlServer;

/**
 * A class to manage the database connection.
 * It is used to create a Connection instance.
 */
class Manager
{
    /**
     * Accepted database drivers
     */
    const DRIVERS = [
        'mysql', 'sqlsrv', 'pgsql', 'sqlite'
    ];

    /**
     * Connection Strings for each database driver
     */
    const CONN_STRINGS = [
        "mysql" => "mysql:host=%s:%s;dbname=%s;charset=utf8mb4",
        "sqlsrv" => "sqlsrv:Server=%s,%s;Database=%s",
        "pgsql" => "pgsql:host=%s;port=%s;dbname=%s",
        "sqlite" => "sqlite:%s"
    ];

    /**
     * Dialect classes mapped to each database driver
     */
    const SQL_DIALECTS = [
        "mysql" => MySql::class,
        "sqlsrv" => SqlServer::class,
        "pgsql" => Postgres::class,
        "sqlite" => SQLite::class
    ];

    /**
     * Connection instance made with the
     * chosen database driver and credentials
     *
     * @var Connection
     */
    protected $connection = null;

    /**
     * Driver chosen for the connection
     *
     * @var string
     */
    protected $driver;

    /**
     * Constructor function for the
     * connection Manager
     *
     * @param string $driver Database driver
     */
    public function __construct(string $driver)
    {
        if (!$this->isValidDriver($driver)) {
            throw new \Exception("'{$driver}' is not a valid connection driver.");
        }

        $this->driver = $driver;
    }

    /**
     * Tries to connect to the database
     * returning, if done so, a Connection instance
     *
     * @param array $options Connection's credentials
     * @return Connection Already connected Connection instance
     */
    public function connect(array $options = null) 
    {
        if(isset($this->connection)) {
            return $this->connection;
        }

        $this->validate($options);
        return $this->connection = $this->getConnection($options);
    }

    /**
     * Checks each credential throwing Exceptions
     * if any of them is not present or incorrect
     *
     * @param array $options Connection's credentials
     * @return void
     */
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

    /**
     * Checks if the chosen driver is present
     * in the possible options to make a connection
     *
     * @param string $driver Database driver
     * @return boolean If driver is valid or not
     */
    protected function isValidDriver(string $driver)
    {
        return in_array($driver, Manager::DRIVERS);
    }

    /**
     * Creates a new Connection instance
     *
     * @param array $options Connection's credentials
     * @return Connection Already connected Connection instance
     */
    protected function getConnection(array $options)
    {
        return (new Connection(
            $this->getConnectionString($options), 
            $options['user'], 
            $options['password']
        ))->setDialect($this->getDialect());
    }

    /**
     * Builds a Connection String based on the
     * credentials passed as parameters
     *
     * @param array $options Connection's credentials
     * @return string a Connection String formatted
     */
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

    /**
     * Returns the dialect that matches to the
     * chosen database driver
     *
     * @return \Pantheion\Database\Dialect\Sql Dialect instance based of the database driver
     */
    protected function getDialect()
    {
        if(!in_array($this->driver, array_keys(Manager::SQL_DIALECTS))) {
            throw new \Exception("'{$this->driver}' is not a valid SQL dialect.");
        }

        $dialect = Manager::SQL_DIALECTS[$this->driver];
        return new $dialect;
    }
}