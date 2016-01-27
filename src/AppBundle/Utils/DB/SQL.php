<?php

namespace AppBundle\Utils\DB;

use DateTimeImmutable;
use Doctrine\DBAL\ConnectionException;
use mysqli;
use mysqli_result;
use mysqli_stmt;
use PDO;
use Symfony\Component\Config\Definition\Exception\InvalidTypeException;

class SQL
{
    /** @var mysqli */
    private static $mysqli;

    private static function __construct()
    {
        self::$mysqli = new mysqli('localhost', 'root', '', 'parallango');
    }

    public static function __destruct()
    {
        self::$mysqli->close();
    }

    /**
     * @return mysqli
     */
    private static function getMysqli()
    {
        if (self::$mysqli === null) {
            new self();
        }
        return self::$mysqli;
    }

    /**
     * @param string $query
     * @param array $params
     * @return bool|mysqli_result
     * @throws ConnectionException
     */
    private static function query($query, array $params)
    {
        if ($error=mysqli_connect_error()) {
            throw new ConnectionException(sprintf(
                'Connect Error (%d) %s',
                mysqli_connect_errno(),
                $error
            ));
        }

        $stmt = self::getMysqli()->prepare($query);
        foreach ($params as $paramName => $paramValue) {
            self::bindParam($stmt, $paramName, $paramValue);
        }
        $res = $stmt->get_result();

        $error = self::getMysqli()->error;
        if (!empty($error)) {
//            exit(sprintf(
//                'SQL error in query [%s].<br>error #%d: %s',
//                $query,
//                self::getMysqli()->errno,
//                $error
//            )); //why Exceptions doesn't work??
            exit(__FILE__.":".__LINE__);
        }

        return $res;
    }

    /**
     * @param string $query
     * @param array $params
     * @return bool
     */
    public static function execute($query, array $params = [])
    {
        return (bool)self::query($query, $params);
    }

    /**
     * @param string $query
     * @param array $params
     * @return array
     */
    public static function toArray($query, array $params = [])
    {
        $array = [];
        if ($res = self::query($query, $params)) {
            $types = self::getRowTypes($res);
            while ($row = mysqli_fetch_assoc($res)) {
                $newRow = [];
                $index = 0;
                foreach ($row as $column => $cell) {
                    $newRow[$column] = self::convertCell(
                        $cell,
                        $types[$index++]
                    );
                }
                $array[] = $newRow;
            }
        }
        return $array;
    }

    /**
     * @param string $query
     * @param array $params
     * @return string
     */
    private static function convertParams($query, array $params)
    {
        $stmt = self::getMysqli()->prepare($query);
        if (in_array('string', array_map('gettype', array_keys($params)))) {
            foreach ($params as $paramName => $paramValue) {

            }
        }
        foreach ($params as $paramName => $paramValue) {
            $query = str_replace(
                ':' . $paramName,
                '\'' . str_replace(
                    ['\\', '\''],
                    ['\\\\', '\\\''],
                    $paramValue
                ) . '\'',
                $query
            );
        }
        return $query;
    }

    /**
     * @param mysqli_stmt $stmt
     * @param string $paramName
     * @param $paramValue
     */
    private static function bindParam(
        mysqli_stmt $stmt,
        $paramName,
        $paramValue
    ) {
        $type = null;
        switch (gettype($paramValue)) {
            case 'null':
                $type = PDO::PARAM_NULL;
                break;
            case 'string':
                $type = PDO::PARAM_STR;
                break;
            case 'bool':
                $type = PDO::PARAM_BOOL;
                break;
            case 'integer':
                $type = PDO::PARAM_INT;
                break;
            case 'float':
                $type = PDO::PARAM_STMT;
                $paramValue = (string)$paramValue;
                break;
            case 'array':
                $type = PDO::PARAM_STMT;
                $paramValue = sprintf(
                    'ARRAY[%s]',
                    implode(', ', array_map(function ($elem) {
                        throw new \Exception();
                    }, $paramValue))
                );
                break;
            default:
                throw new InvalidTypeException();
        }
        $stmt->bind_param(':' . $paramName, $paramValue, $type);
    }

    /**
     * @param mysqli_result $result
     * @return string[]
     */
    private static function getRowTypes(mysqli_result $result)
    {
        $types = [];
        if ($result->field_count > 0) {
            foreach (range(0, $result->field_count - 1) as $index) {
                $types[] = $result->fetch_field_direct($index)->type;
            }
        }
        return $types;
    }

    /**
     * @param string $oldValue
     * @param string $newType
     * @return mixed
     */
    private static function convertCell($oldValue, $newType)
    {
        if ($oldValue === null) {
            return null;
        }
        switch ($newType) {
            case 'int':
                return (int)$oldValue;
            case 'float':
                return floatval($oldValue);
            case 'double':
                return doubleval($oldValue);
            case 'datetime':
                return new DateTimeImmutable($oldValue);
            case 'varchar':
                return (string)$oldValue;
            default:
                throw new InvalidTypeException();
        }
    }
}
