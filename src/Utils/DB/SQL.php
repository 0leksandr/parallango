<?php

namespace Utils\DB;

use Exception;
use PDO;
use Utils\DB\Exception\DBException;

class SQL
{
    /** @var PDO */
    private $pdo;

    /**
     * @param string $host
     * @param string $dbname
     * @param string $user
     * @param string $pass
     */
    public function __construct($host, $dbname, $user, $pass)
    {
        $driver = 'mysql';
        $charset = 'utf8';
        $this->pdo = new PDO(
            sprintf(
                '%s:host=%s;dbname=%s;charset=%s',
                $driver,
                $host,
                $dbname,
                $charset
            ),
            $user,
            $pass,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_EMULATE_PREPARES => 0, // multiple queries
            ]
        );
    }

    /**
     * @param string $query
     * @return Statement
     */
    public function prepare($query)
    {
        return new Statement($this->pdo, $query);
    }

    /**
     * @param string $query
     * @param array $params
     */
    public function execute($query, array $params = [])
    {
        $this
            ->prepare($query)
            ->execute($params);
    }

    /**
     * @param string $query
     * @param array $params
     * @return array[]
     */
    public function getArray($query, array $params = [])
    {
        return $this
            ->prepare($query)
            ->execute($params)
            ->getArray();
    }

    /**
     * @param string $query
     * @param array $params
     * @return array|null
     */
    public function getRow($query, array $params = [])
    {
        return $this
            ->prepare($query)
            ->execute($params)
            ->getRow();
    }

    /**
     * @param string $query
     * @param array $params
     * @param int|string $indexOrTitle
     * @return array
     */
    public function getColumn(
        $query,
        array $params = [],
        $indexOrTitle = 0
    ) {
        return $this
            ->prepare($query)
            ->execute($params)
            ->getColumn($indexOrTitle);
    }

    /**
     * @param string $query
     * @param array $params
     * @param int|string $indexOrTitle
     * @return mixed
     */
    public function getSingle(
        $query,
        array $params = [],
        $indexOrTitle = 0
    ) {
        return $this
            ->prepare($query)
            ->execute($params)
            ->getSingle($indexOrTitle);
    }

    /**
     * @return int
     */
    public function lastInsertId()
    {
        return (int)$this->pdo->lastInsertId();
    }

    /**
     * @param Exception $ex
     * @param string $query
     * @param array|null $parameters
     * @throws DBException
     */
    public static function reThrowEx(
        Exception $ex,
        $query,
        array $parameters = null
    ) {
        $message = '';

        // TODO: do not show in PROD
        if (true) {
            $message .= sprintf(
                <<<'TEXT'

Exception class: %s

Original trace:
%s

Query:
%s

TEXT
                ,
                get_class($ex),
                $ex->getTraceAsString(),
                $query
            );
            if ($parameters !== null) {
                $message .= sprintf(
                    <<<'TEXT'

Parameters:
%s

TEXT
                    ,
                    print_r($parameters, true)
                );
            }
        }

        $message .= sprintf(
            <<<'TEXT'

Message:
%s
TEXT
            ,
            $ex->getMessage()
        );

        throw new DBException($message);
    }
}
