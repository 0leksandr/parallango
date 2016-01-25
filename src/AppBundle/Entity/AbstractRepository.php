<?php

namespace AppBundle\Entity;

abstract class AbstractRepository
{
    /** @var Identifiable[] */
    private $entities = [];

    /**
     * @param int[] $ids
     * @return Identifiable[]
     */
    abstract protected function fetch(array $ids);

    /**
     * @param int $id
     * @return Identifiable
     */
    public function getById($id)
    {
        $entity = $this->fetch([$id])[0];
        $this->keep([$entity]);
        return $entity;
    }

    /**
     * @param int[] $ids
     * @return Identifiable[]
     */
    public function getByIds(array $ids)
    {
        $entities = $this->fetch($ids);
        $this->keep($entities);
        return $entities;
    }

    /**
     * @param Identifiable[] $entities
     * @return $this
     */
    protected function keep(array $entities)
    {
        foreach ($entities as $entity) {
            $this->entities[$entity->getId()] = $entity;
        }
        return $this;
    }
}
