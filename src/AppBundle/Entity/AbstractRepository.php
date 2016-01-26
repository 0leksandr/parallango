<?php

namespace AppBundle\Entity;

use Exception;
use mysqli;
use Symfony\Component\Routing\Exception\MissingMandatoryParametersException;

abstract class AbstractRepository
{
    /** @var Identifiable[] */
    private $entities = [];

    /**
     * @return string
     */
    abstract protected function getEntityClass();

    /**
     * return string[] [$fieldName, ]
     */
    protected function constructorMapping()
    {
        return [];
    }

    /**
     * @return string[] [$fieldName => $setterMethodName, ]
     */
    protected function settersMapping()
    {
        return [];
    }

    /**
     * @param array $data
     * @return Identifiable
     */
    protected function createByData(array $data)
    {
        $constructorArgs = [];
        foreach ($this->constructorMapping() as $fieldName) {
            $constructorArgs[] =
                $this->getMandatoryFieldValue($data, $fieldName);
        }

        /** @var Identifiable $entity */
        $entity = (new \ReflectionClass($this->getEntityClass()))
            ->newInstanceArgs($constructorArgs);
        foreach ($this->settersMapping() as $fieldName => $setter) {
            $entity->$setter($this->getMandatoryFieldValue($data, $fieldName));
        }

        $this->keep($entity);

        return $entity;
    }

    /**
     * @param string $query
     * @param array $params
     * @param int|null $expectedAmount
     * @return Identifiable[]
     * @throws Exception
     */
    protected function getManyByQuery(
        $query,
        array $params = [],
        $expectedAmount = null
    ) {
        $entities = [];
        foreach ($this->getSqlDataByQuery($query, $params) as $row) {
            $entities[] = $this->createByData($row);
        }
        if ($expectedAmount !== null && count($entities) !== $expectedAmount) {
            throw new Exception(sprintf(
                '%d %s objects were generated instead of %d',
                count($entities),
                $this->getEntityClass(),
                $expectedAmount
            ));
        }
        return $entities;
    }

    /**
     * @param string $query
     * @param array $params
     * @return Identifiable
     * @throws Exception
     */
    protected function getOneByQuery($query, array $params = [])
    {
        $entities = $this->getManyByQuery($query, $params);
        if (count($entities) === 0) {
            throw new Exception(sprintf(
                'Can not create %s object',
                $this->getEntityClass()
            ));
        }
        if (count($entities) > 1) {
            throw new Exception(sprintf(
                'Multiple %s objects were created, instead of one',
                $this->getEntityClass()
            ));
        }
        return $entities[0];
    }

    /**
     * @param array $data
     * @param string $fieldName
     * @return mixed
     */
    private function getMandatoryFieldValue(array $data, $fieldName)
    {
        if (!isset($data[$fieldName])) {
            throw new MissingMandatoryParametersException();
        }

        return $data[$fieldName];
    }

    /**
     * @param Identifiable $entity
     */
    protected function keep(Identifiable $entity)
    {
        $this->entities[$entity->getId()] = $entity;
    }

    /**
     * @param string $query
     * @param array $params
     * @return string[][]
     */
    private function getSqlDataByQuery($query, array $params = [])
    {
        $mysqli=new mysqli();
        $mysqli->connect('127.0.0.1','root','','parallango');
        $mysqli->query('SET NAMES utf8');

        foreach ($params as $paramName => $paramValue) {
            $query = str_replace(
                ':' . $paramName,
                '\'' . str_replace(["\\"], ["\\\\"], $paramValue) . '\'',
                $query
            );
        }

        $data = [];
        $res = $mysqli->query($query);
        while ($row = mysqli_fetch_assoc($res)) {
            $data[] = $row;
        }

        $mysqli->close();
        return $data;
    }
}
