<?php

namespace AppBundle\Utils;

use Doctrine\DBAL\ConnectionException;
use mysqli;
use mysqli_result;

class Sql
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
    public static function query($query, array $params = [])
    {
        if ($error=mysqli_connect_error()) {
            throw new ConnectionException(sprintf(
                'Connect Error (%d) %s',
                mysqli_connect_errno(),
                $error
            ));
        }

        $res = self::getMysqli()->query(self::convertParams($query, $params));
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
     * @return array
     */
    public static function toArray($query, array $params = [])
    {
        $array = [];
        $res = self::query($query, $params);
        while ($row = mysqli_fetch_assoc($res)) {
            $array[] = $row;
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
}
