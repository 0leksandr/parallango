<?php

namespace AppBundle\Entity\Author;

use AppBundle\Entity\AbstractRepository;

class Repository extends AbstractRepository
{
    /**
     * @return string
     */
    protected function getEntityClass()
    {
        return Author::class;
    }

    protected function createByData(array $data)
    {

    }
}
