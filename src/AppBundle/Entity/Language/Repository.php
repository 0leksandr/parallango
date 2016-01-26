<?php

namespace AppBundle\Entity\Language;

use AppBundle\Entity\AbstractRepository;

class Repository extends AbstractRepository
{
    /**
     * @return string
     */
    protected function getEntityClass()
    {
        return Language::class;
    }

    /**
     * @return string[]
     */
    protected function constructorMapping()
    {
        return ['id', 'code'];
    }

    /**
     * @param string $code
     * @return Language
     */
    public function getByCode($code)
    {
        return $this->getOneByQuery(
            <<<'SQL'
            SELECT *
            FROM languages
            WHERE code = :code
SQL
            ,
            [
                'code' => $code,
            ]
        );
    }
}
