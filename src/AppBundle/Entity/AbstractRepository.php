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
     * @param array $data
     * @return Identifiable
     */
    protected function getByData(array $data)
    {
        return $this->getByIdData($data['id'], $data);
    }

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
                        'Missing required field %s',
                        $field
                    ));
                }
            }
        }
    }

    /**
     * @param array $rows
     * @return int|string
     * @throws Exception
     */
    protected function getIdFromMultipleRows(array $rows)
    {
        $ids = array_unique(ipull($rows, 'id'));
        if (count($ids) !== 1) {
            throw new Exception(sprintf(
                'Incorrect data provided for %s',
                get_class($this)
            ));
        }
        return head($ids);
    }
}
