<?php

namespace AppBundle\Entity;

use Exception;

abstract class AbstractRepository
{
    /** @var Identifiable[] */
    private $entities = [];

    /**
     * @param array $data
     * @return Identifiable
     */
    abstract protected function createByData(array $data);

    /**
     * @param int $id
     * @param array $data
     * @return Identifiable
     */
    protected function getByIdData($id, array $data)
    {
        if (isset($this->entities[$id])) {
            return $this->entities[$id];
        }
        $entity = $this->createByData($data);

        $this->checkId($id, $entity);

        $this->entities[$entity->getId()] = $entity;

        return $entity;
    }

    /**
     * @param array $array
     * @param string[] $fields
     * @throws Exception
     */
    protected function mandatory(array $array, array $fields)
    {
        if (!is_array(head($array))) {
            $array = [$array];
        }
        foreach ($array as $row) {
            foreach ($fields as $field) {
                if (!isset($row[$field])) {
                    throw new Exception(sprintf(
                        <<<'TEXT'
Missing required field %s. Available fields: %s.
Row: %s
TEXT
                        ,
                        $field,
                        implode(', ', array_keys($row)),
                        print_r($row, true)
                    ));
                }
            }
        }
    }

    /**
     * @param array $rows
     * @param string $key
     * @return mixed
     * @throws Exception
     */
    protected function getValueFromMultipleRows(array $rows, $key) // TODO: remove?
    {
        $values = array_unique(ipull($rows, $key));
        if (count($values) !== 1) {
            throw new Exception(sprintf(
                'Incorrect data provided for %s',
                get_class($this)
            ));
        }
        return head($values);
    }

    /**
     * @param array $rows
     * @return int|string
     */
    protected function getIdFromMultipleRows(array $rows) // TODO: remove?
    {
        return $this->getValueFromMultipleRows($rows, 'id');
    }

    /**
     * @param int $id
     * @param Identifiable $entity
     * @throws Exception
     */
    protected function checkId($id, Identifiable $entity)
    {
        if ($id !== $entity->getId()) {
            throw new Exception(sprintf(
                'Requested id %d !== generated %d',
                $id,
                $entity->getId()
            ));
        }
    }
}
