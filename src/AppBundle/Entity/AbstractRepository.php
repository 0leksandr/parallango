<?php

namespace AppBundle\Entity;

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
     * @throws \Exception
     */
    protected function mandatory(array $array, array $fields)
    {
        foreach ($fields as $field) {
            if (!isset($array[$field])) {
                throw new \Exception(sprintf(
                    'Missing required field %s',
                    $field
                ));
            }
        }
    }
}
