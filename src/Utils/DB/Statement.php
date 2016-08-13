<?php

namespace Utils\DB;

use PDO;
use PDOException;
use PDOStatement;
use Utils\DB\Exception\DBException;

require_once __DIR__ . '/../Utils.php';

class Statement
{
    const MAX_LIMIT = 9223372036854775807;// 18446744073709551615;
    const MAX_NR_PARAMETERS = 65535;

    /** @var PDO */
    private $pdo;
    /** @var string */
    private $query;
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
     * @return Result
     */
    public function execute(array $params = [])
    {
        $originalQuery = $this->query;

        $this->replaceLiterals($params);

        foreach ($params as $paramName => $paramValue) {
            $this->prepareParamToBind($paramName, $paramValue);
        }
        if (
            count($this->paramsToBind) !==
            count(array_unique(ipull($this->paramsToBind, 'name')))
        ) {
            SQL::reThrowEx(
                new DBException('Inconsistency in params names'),
                $this->query
            );
        }
        $this->checkMaxNrParams(count($this->paramsToBind));
        try {
            $statement = $this->pdo->prepare($this->query);
        } catch (PDOException $ex) {
            SQL::reThrowEx($ex, $this->query);
        }
        /** @var PDOStatement $statement */
        foreach ($this->paramsToBind as $param) {
            try {
                $statement->bindValue(
                    $param['name'],
                    $param['value'],
                    $param['type']
                );
            } catch (PDOException $ex) {
                if (!in_array($ex->getCode(), [
                    'HY093',
                ]) && !in_array($ex->getMessage(), [
                    'Invalid parameter number: parameter was not defined',
                ])) {
                    SQL::reThrowEx($ex, $this->query, $params);
                }
            }
        }

        $executed = false;
        try {
            $executed = $statement->execute();
        } catch (PDOException $ex) {
            SQL::reThrowEx($ex, $this->query, $params);
        }
        if ($executed === false) {
            SQL::reThrowEx(
                new DBException('Statement was not executed successfully'),
                $this->query
            );
        }

        $this->query = $originalQuery;
        $this->paramsToBind = [];

        return new Result($statement);
    }

    /**
     * @param int $nrParams
     * @throws DBException
     */
    private function checkMaxNrParams($nrParams)
    {
        if ($nrParams > self::MAX_NR_PARAMETERS) {
            throw new DBException(sprintf(
                'Too many parameters in query: %d',
                $nrParams
            ));
        }
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
        if ($paramName === 'LIMIT' && $paramValue === null) {
            $paramValue = self::MAX_LIMIT;
        }
    }

    /**
     * @param string $paramName
     * @param array $array
     * @param string $mask
     */
    private function prepareArrayToBind($paramName, array $array, $mask)
    {
        $this->checkMaxNrParams(count($array, COUNT_RECURSIVE));
        $newArray = [];
        foreach (array_values($array) as $index => $value) {
            $newArray[sprintf('%s_%d_', $paramName, $index)] = $value;
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
            $this->prepareParamToBind($key, $value);
        }
    }

    /**
     * @param string $paramName
     * @param mixed $paramValue
     * @throws DBException
     */
    private function prepareParamToBind($paramName, $paramValue)
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
            case '__boolean': // TODO: fix
                $pdoType = PDO::PARAM_BOOL;
                break;
            case 'integer':
            case 'boolean':
                $pdoType = PDO::PARAM_INT;
                break;
            case 'array':
                $this->prepareArrayToBind($paramName, $paramValue, '(%s)');
                return;
            case 'object':
                if ($paramValue instanceof ValuesList) {
                    $this->prepareArrayToBind(
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
}
