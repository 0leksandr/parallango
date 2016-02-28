<?php

namespace Utils\DB;

use PDO;
use PDOException;
use PDOStatement;

require_once __DIR__ . '/../Utils.php';

class Statement
{
    const MAX_LIMIT = 18446744073709551615;

    /** @var PDOStatement */
    private $statement;
    /** @var PDO */
    private $pdo;
    /** @var string */
    private $query;
    /** @var string[][] */
    private $columnMetas;
    /** @var bool */
    private $executed = false;
    /** @var array[] */
    private $paramsToBind = [];

    /**
     * @param PDO $pdo
     * @param string $query
     */
    public function __construct(PDO $pdo, $query)
    {
        $this->pdo = $pdo;
        $this->query = $query;
    }

    /**
     * @param array $params
     * @return $this
     * @throws DBException
     */
    public function execute(array $params = [])
    {
        $this->replaceLiterals($params);

        foreach ($params as $paramName => $paramValue) {
            $this->bindParam($paramName, $paramValue);
        }

        $this->statement = $this->pdo->prepare($this->query);
        if (
            count($this->paramsToBind) !==
            count(array_unique(ipull($this->paramsToBind, 'name')))
        ) {
            throw new DBException('Inconsistency in params names');
        }
        foreach ($this->paramsToBind as $param) {
            $this->statement->bindValue(
                $param['name'],
                $param['value'],
                $param['type']
            );
        }

        try {
            $executed = $this->statement->execute();
        } catch (PDOException $ex) {
            $this->reThrowEx($ex);
            exit(__FILE__ . ':' . __LINE__ . PHP_EOL); // shut up, storm!
        }
        if ($executed === false) {
            throw new DBException();
        }

        $this->executed = true;

        return $this;
    }

    /**
     * @return array[]
     */
    public function getArray()
    {
        $this->checkExecuted();

        $array = [];
        while ($row = $this->fetch()) {
            $array[] = $row;
        }
        return $array;
    }

    /**
     * @return array|null
     */
    public function getRow()
    {
        $this->checkExecuted();
        return $this->fetch();
    }

    /**
     * @param int|string $indexOrTitle
     * @return array
     */
    public function getColumn($indexOrTitle = 0)
    {
        $this->checkExecuted();

        $array = $this->getArray();
        $indexOrTitle = $this->getColumnIndex($indexOrTitle);
        $column = [];
        foreach ($array as $row) {
            $column[] = array_values($row)[$indexOrTitle];
        }

        return $column;
    }

    /**
     * @param int|string $indexOrTitle
     * @return mixed
     */
    public function getSingle($indexOrTitle = 0)
    {
        $this->checkExecuted();

        $indexOrTitle = $this->getColumnIndex($indexOrTitle);
        $cell = $this->statement->fetchColumn($indexOrTitle);
        $type = $this->getColumnTypes()[$indexOrTitle];

        return self::convertCell($cell, $type);
    }

    /**
     * @return array|null
     */
    public function fetch()
    {
        $this->checkExecuted();

        try {
            if ($row = $this->statement->fetch(PDO::FETCH_ASSOC)) {
                return $this->rowToArray($row);
            }
        } catch (PDOException $ex) {
            $this->reThrowEx($ex);
        }

        return null;
    }

    /**
     * @throws DBException
     */
    private function checkExecuted()
    {
        if (!$this->executed) {
            throw new DBException('Statement is not executed');
        }
    }

    /**
     * @param PDOException $ex
     * @throws DBException
     */
    private function reThrowEx(PDOException $ex)
    {
        throw new DBException(sprintf(
            <<<'TEXT'
Query:
%s

Message:
%s
TEXT
            ,
            $this->query,
            $ex->getMessage()
        ));
    }

    /**
     * @param string[] $row
     * @return array
     */
    private function rowToArray(array $row)
    {
        $newRow = [];
        $index = 0;
        $types = $this->getColumnTypes();
        foreach ($row as $column => $cell) {
            $newRow[$column] = self::convertCell(
                $cell,
                $types[$index++]
            );
        }
        return $newRow;
    }

    /**
     * @param int|string $indexOrTitle
     * @return int
     * @throws DBException
     */
    private function getColumnIndex($indexOrTitle)
    {
        if (is_string($indexOrTitle)) {
            $columnNames = $this->getColumnNames();
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
     * @return string[]
     */
    private function getColumnNames()
    {
        return ipull($this->getColumnMetas(), 'name');
    }

    /**
     * @return string[]
     */
    private function getColumnTypes()
    {
        return ipull($this->getColumnMetas(), 'native_type');
    }

    /**
     * @return string[][]
     */
    private function getColumnMetas()
    {
        if (!isset($this->columnMetas)) {
            $this->columnMetas = [];
            if (($nrColumns = $this->statement->columnCount()) > 0) {
                foreach (range(0, $nrColumns - 1) as $index) {
                    $this->columnMetas[] =
                        $this->statement->getColumnMeta($index);
                }
            }
        }
        return $this->columnMetas;
    }

    /**
     * @param array $params
     */
    private function replaceLiterals(array &$params)
    {
        foreach ($params as $paramName => $paramValue) {
            if (is_object($paramValue) && $paramValue instanceof Literal) {
                $this->query = str_replace(
                    ':' . $paramName,
                    (string)$paramValue,
                    $this->query
                );
                unset($params[$paramName]);
            }
        }
    }

    /**
     * @param string $paramName
     * @param mixed $paramValue
     */
    private static function replaceLimit($paramName, &$paramValue)
    {
        if ($paramName === 'limit' && $paramValue === null) {
            $paramValue = self::MAX_LIMIT;
        }
    }

    /**
     * @param string $paramName
     * @param array $array
     * @param string $mask
     */
    private function bindArray($paramName, array $array, $mask)
    {
        $newArray = [];
        foreach (array_values($array) as $index => $value) {
            $newArray[sprintf('%s_%d_end', $paramName, $index)] = $value;
        }
        $this->query = str_replace(
            ':' . $paramName,
            sprintf(
                $mask,
                implode(',', array_map(function ($key) {
                    return ':' . $key;
                }, array_keys($newArray)))
            ),
            $this->query
        );

        foreach ($newArray as $key => $value) {
            $this->bindParam($key, $value);
        }
    }

    /**
     * @param string $paramName
     * @param mixed $paramValue
     * @throws DBException
     */
    private function bindParam($paramName, $paramValue)
    {
        self::replaceLimit($paramName, $paramValue);
        $pdoType = null;
        $phpType = gettype($paramValue);
        switch ($phpType) {
            case 'NULL':
                $pdoType = PDO::PARAM_NULL;
                break;
            case 'string':
            case 'double':
                $pdoType = PDO::PARAM_STR;
                break;
            case 'boolean':
                $pdoType = PDO::PARAM_BOOL;
                break;
            case 'integer':
                $pdoType = PDO::PARAM_INT;
                break;
            case 'array':
                $this->bindArray($paramName, $paramValue, '(%s)');
                return;
            case 'object':
                if ($paramValue instanceof ValuesList) {
                    $this->bindArray(
                        $paramName,
                        $paramValue->getValues(),
                        '%s'
                    );
                    return;
                }
                throw new DBException(
                    'Invalid class: ' . get_class($paramValue)
                );
            default:
                throw new DBException(sprintf('Invalid type: %s', $phpType));
        }

        $this->paramsToBind[] = [
            'name' => ':' . $paramName,
            'value' => $paramValue,
            'type' => $pdoType,
        ];
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
            case 'LONG':
                return (int)$oldValue;
            case 'VAR_STRING':
            case 'BLOB':
                return (string)$oldValue;
            case 'NEWDECIMAL':
                return floatval($oldValue);
            case 'DOUBLE':
                return doubleval($oldValue);
            case 'TINY':
                return (bool)$oldValue;
            default:
                throw new DBException(sprintf('Invalid type: %s', $newType));
        }
    }
}
