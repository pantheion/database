<?php

namespace Pantheion\Database;

use Pantheion\Database\Type\DateTime;
use Pantheion\Database\Type\Type;
use Pantheion\Facade\Arr;
use Pantheion\Facade\Str;
use PDOStatement;
use PDO;

/**
 * Represents a statement to be executed by
 * the current database Connection
 */
class Statement
{
    /**
     * PDOStatment Instance
     *
     * @var PDOStatement
     */
    protected $pdoStatement;


    /**
     * Parameters to be bound to this statement
     *
     * @var array|null
     */
    protected $params;

    /**
     * Statement constructor function
     *
     * @param PDOStatement $pdoStatement Instance of a PDOStatement
     * @param array $params Parameters to be bound to the statement
     */
    public function __construct(PDOStatement $pdoStatement, array $params = [])
    {
        $this->pdoStatement = $pdoStatement;
        $this->params = $params;
    }

    /**
     * Binds the parameters and calls
     * for a resolution of the current Statement
     *
     * @return mixed Statement's resolution
     */
    public function execute()
    {
        if(!Arr::empty($this->params)) {
            foreach($this->params as $i => $param) {
                $this->bind($param, $i);
            }
        }

        return $this->resolve();
    }

    /**
     * Binds the parameter to the PDO statement
     * based of its value type
     *
     * @param mixed $param Parameter's value
     * @param integer $i Paramenter's position
     * @return bool Returns if the biding was succesful or not
     */
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

    /**
     * Resolves the Statement's result based
     * on what was returned from the PDO's execution
     *
     * @return mixed Result from the statement
     */
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

        return $this->pdoStatement->rowCount() > 0 ? $this->pdoStatement->fetchAll(PDO::FETCH_ASSOC) : [];
    }
}