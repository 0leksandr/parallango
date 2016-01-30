<?php

namespace AppBundle\Utils\DB;

use PDO;
use PDOStatement;

class SQL
{
    /** @var PDO */
    private $pdo;

    public function __construct()
    {
        $this->pdo = new PDO(
            'mysql:host=localhost;dbname=parallango;charset=utf8',
            'root',
            '',
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
    }

    /**
     * @param string $query
     * @param array $params
     */
    public function execute($query, array $params = [])
    {
        $this->getExecutedStmt($query, $params);
    }

    /**
     * @param string $query
     * @param array $params
     * @return array|null
     */
    public function getArray($query, array $params = [])
    {
        if (($stmt = $this->getExecutedStmt($query, $params)) === null) {
            return null;
        }
        return self::getArrayByStmt($stmt);
    }

    /**
     * @param string $query
     * @param array $params
     * @return array|null
     */
    public function getRow($query, array $params = [])
    {
        if (($stmt = $this->getExecutedStmt($query,$params)) === null) {
            return null;
        }
        return self::fetchNextRow($stmt, self::getColumnTypes($stmt));
    }

    /**
     * @param string $query
     * @param array $params
     * @param int|string $indexOrTitle
     * @return array|null
     */
    public function getColumn(
        $query,
        array $params = [],
        $indexOrTitle = 0
    ) {
        if (($stmt = $this->getExecutedStmt($query, $params)) === null) {
            return null;
        }
        $array = self::getArrayByStmt($stmt);
        $indexOrTitle = self::getColumnIndex($stmt, $indexOrTitle);
        $column = [];
        foreach ($array as $row) {
            $column[] = array_values($row)[$indexOrTitle];
        }
        return $column;
    }

    /**
     * @param string $query
     * @param array $params
     * @param int|string $indexOrTitle
     * @return mixed|null
     * @throws DBException
     */
    public function getSingle(
        $query,
        array $params = [],
        $indexOrTitle = 0
    ) {
        if (($stmt = $this->getExecutedStmt($query, $params)) === null) {
            return null;
        }
        $indexOrTitle = self::getColumnIndex($stmt, $indexOrTitle);
        $cell = $stmt->fetchColumn($indexOrTitle);

        return self::convertCell(
            $cell,
            self::getColumnTypes($stmt)[$indexOrTitle]
        );
    }

    /**
     * @param string $query
     * @param array $params
     * @return PDOStatement|null
     * @throws DBException
     */
    private function getExecutedStmt($query, array $params)
    {
        self::replaceManually($query, $params);

        $stmt = $this->pdo->prepare($query);
        if ($stmt === false) {
            throw new DBException();
        }

        foreach ($params as $paramName => $paramValue) {
            self::bindParam($stmt, $paramName, $paramValue);
        }

        if (!$stmt->execute()) {
            throw new DBException();
        }

        if ($stmt->rowCount() === 0) {
            return null;
        }

        return $stmt;
    }

    /**
     * @param PDOStatement $stmt
     * @return array
     */
    private static function getArrayByStmt(PDOStatement $stmt)
    {
        $types = self::getColumnTypes($stmt);
        $array = [];
        while ($row = self::fetchNextRow($stmt, $types)) {
            $array[] = $row;
        }
        return $array;
    }

    /**
     * @param PDOStatement $stmt
     * @param string[] $types
     * @return array|null
     */
    private static function fetchNextRow(PDOStatement $stmt, array $types)
    {
        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            return self::rowToArray($row, $types);
        }
        return null;
    }

    /**
     * @param array $row
     * @param string[] $types
     * @return array
     */
    private static function rowToArray(array $row, array $types)
    {
        $newRow = [];
        $index = 0;
        foreach ($row as $column => $cell) {
            $newRow[$column] = self::convertCell(
                $cell,
                $types[$index++]
            );
        }
        return $newRow;
    }

    /**
     * @param PDOStatement $stmt
     * @param int|string $indexOrTitle
     * @return int
     * @throws DBException
     */
    private static function getColumnIndex(PDOStatement $stmt, $indexOrTitle)
    {
        if (is_string($indexOrTitle)) {
            $columnNames = self::getColumnNames($stmt);
            $keys = array_keys($columnNames, $indexOrTitle);
            if (count($keys) !== 1) {
                throw new DBException(sprintf(
                    'Column name "%s" is invalid',
                    $indexOrTitle
                ));
            }
            $indexOrTitle = $keys[0];
            return $indexOrTitle;
        }
        return $indexOrTitle;
    }

    /**
     * @param PDOStatement $stmt
     * @return string[]
     */
    private static function getColumnTypes(PDOStatement $stmt)
    {
        return self::getColumnMetas($stmt, 'native_type');
    }

    /**
     * @param PDOStatement $stmt
     * @return string[]
     */
    private static function getColumnNames(PDOStatement $stmt)
    {
        return self::getColumnMetas($stmt, 'name');
    }

    /**
     * @param PDOStatement $stmt
     * @param string $metaName
     * @return string[]
     */
    private static function getColumnMetas(PDOStatement $stmt, $metaName)
    {
        $metas = [];
        if (($nrColumns = $stmt->columnCount()) > 0) {
            foreach (range(0, $nrColumns - 1) as $index) {
                $metas[] = $stmt->getColumnMeta($index)[$metaName];
            }
        }
        return $metas;
    }

    /**
     * @param string $query
     * @param array $params
     */
    private static function replaceManually(&$query, array &$params)
    {
        foreach ($params as $paramName => $paramValue) {
            $type = gettype($paramValue);
            if (
                $type === 'double'
                || ($type === 'object' && $paramValue instanceof Literal)
            ) {
                $query = str_replace(
                    ':' . $paramName,
                    (string)$paramValue,
                    $query
                );
                unset($params[$paramName]);
            }
        }
    }

    /**
     * @param string $oldValue
     * @param string $newType
     * @return mixed
     * @throws DBException
     */
    private static function convertCell($oldValue, $newType)
    {
        if ($oldValue === null) {
            return null;
        }
        switch ($newType) {
            case 'LONGLONG':
                return (int)$oldValue;
            case 'VAR_STRING':
                return (string)$oldValue;
            case 'NEWDECIMAL':
                return floatval($oldValue);
            default:
                throw new DBException(sprintf('Invalid type: %s', $newType));
        }
    }

    /**
     * @param PDOStatement $stmt
     * @param string $paramName
     * @param $paramValue
     * @throws DBException
     */
    private static function bindParam(
        PDOStatement $stmt,
        $paramName,
        $paramValue
    ) {
        $pdoType = null;
        $phpType = gettype($paramValue);
        switch ($phpType) {
            case 'NULL':
                $pdoType = PDO::PARAM_NULL;
                break;
            case 'string':
                $pdoType = PDO::PARAM_STR;
                break;
            case 'boolean':
                $pdoType = PDO::PARAM_BOOL;
                break;
            case 'integer':
                $pdoType = PDO::PARAM_INT;
                break;
            default:
                throw new DBException(sprintf('Invalid type: %s', $phpType));
        }

        $stmt->bindValue(':' . $paramName, $paramValue, $pdoType);
    }
}
