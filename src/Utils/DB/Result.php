<?php

namespace Utils\DB;

use PDO;
use PDOException;
use PDOStatement;
use Utils\DB\Exception\DBException;
use Utils\DB\Result\Batch\ResultBatchIndexed;
use Utils\DB\Result\Batch\ResultBatchSized;

require_once __DIR__ . '/../Utils.php';

class Result
{
    const NO_SINGLE_VALUE = 'NO_SINGLE_VALUE';

    /** @var PDOStatement */
    private $statement;
    /** @var string[][] */
    private $columnMetas;

    /**
     * @param PDOStatement $statement
     */
    public function __construct(PDOStatement $statement)
    {
        $this->statement = $statement;
    }

    /**
     * @return array[]
     */
    public function getArray()
    {
        $array = [];
        while (($row = $this->fetch()) !== null) {
            $array[] = $row;
        }
        return $array;
    }

    /**
     * @return array|null
     */
    public function getRow()
    {
        return $this->fetch();
    }

    /**
     * @param int|string $indexOrTitle
     * @return array
     */
    public function getColumn($indexOrTitle = 0)
    {
        // TODO: check for multi-columns result
        $indexOrTitle = $this->getColumnIndex($indexOrTitle);
        $column = [];
        while (true) {
            $single = $this->getSingle($indexOrTitle);
            if ($single === self::NO_SINGLE_VALUE) {
                break;
            }
            $column[] = $single;
        }

        return $column;
    }

    /**
     * @param int|string $indexOrTitle
     * @return mixed
     * @throws DBException
     */
    public function getSingle($indexOrTitle = 0)
    {
        $indexOrTitle = $this->getColumnIndex($indexOrTitle);
        if ($indexOrTitle >= count($this->getColumnMetas())) {
            throw new DBException('Incorrect column index');
        }
        $cell = $this->statement->fetchColumn($indexOrTitle);
        if ($cell === false) {
            return self::NO_SINGLE_VALUE;
        }
        $type = $this->getColumnTypes()[$indexOrTitle];

        return self::convertCell($cell, $type);
    }

    /**
     * @return array|null
     */
    public function fetch()
    {
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
     * @param string $indexColumn
     * @return ResultBatchIndexed
     */
    public function getResultBatchIndexed($indexColumn)
    {
        return new ResultBatchIndexed($this, $indexColumn);
    }

    /**
     * @return ResultBatchSized
     */
    public function getResultBatchSized()
    {
        return new ResultBatchSized($this);
    }

    /**
     * @param PDOException $ex
     * @throws DBException
     */
    private function reThrowEx(PDOException $ex)
    {
        throw new DBException(sprintf(
            <<<'TEXT'
Message:
%s
TEXT
            ,
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
            case 'STRING':
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
