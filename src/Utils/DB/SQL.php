<?php

namespace Utils\DB;

use PDO;

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
}
