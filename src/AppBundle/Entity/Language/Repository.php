<?php

namespace AppBundle\Entity\Language;

use Doctrine\DBAL\Connection;

class Repository
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
        $stmt = $this->dbal->prepare(
            <<<'SQL'
            SELECT 1
            FROM `_languages`
SQL
        );
    }
}
