<?php

namespace AppBundle\Entity;

abstract class Identifiable
{
    /** @var int */
    private $id;

    /**
     * @param $id
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
}
