<?php

namespace AppBundle\Entity;

abstract class Identifiable
{
    /** @var int */
    private $id;

    /**
     * @param int $id
     */
    public function __construct($id)
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param Identifiable[] $entities
     * @return Identifiable[]
     */
    public static function map(array $entities)
    {
        $map = [];
        foreach ($entities as $entity) {
            $map[$entity->getId()] = $entity;
        }
        return $map;
    }
}
