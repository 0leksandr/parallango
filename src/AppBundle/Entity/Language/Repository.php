<?php

namespace AppBundle\Entity\Language;

use AppBundle\Entity\AbstractRepository;
use AppBundle\Entity\Identifiable;
use AppBundle\Entity\Language;
use Symfony\Component\Intl\Exception\MethodNotImplementedException;

class Repository extends AbstractRepository
{
    /** @var Connection */
    private $dbal;

    /**
     * @param Connection $dbal
     */
    public function __construct(Connection $dbal)
    {
        $this->dbal = $dbal;
    }
    
    public function getByCode($code)
    {
    }

    /**
     * @param int[] $ids
     * @return Identifiable[]
     */
    protected function fetch(array $ids)
    {
        throw new MethodNotImplementedException(__METHOD__);
    }
}
