<?php

namespace AppBundle\Entity\Language;

use AppBundle\Entity\AbstractRepository;
use Utils\DB\SQL;

class LanguageRepository extends AbstractRepository
{
    /** @var SQL */
    private $sql;

    /**
     * @param SQL $sql
     */
    public function __construct(SQL $sql)
    {
        $this->sql = $sql;
    }

    /**
     * @param string $code
     * @return Language
     */
    public function getByCode($code)
    {
        return $this->getByData($this->sql->getRow(
            <<<'SQL'
            SELECT id, code
            FROM languages
            WHERE code = :code
SQL
            ,
            ['code' => $code,]
        ));
    }

    /**
     * @return Language[]
     */
    public function getAll()
    {
        $rows = $this->sql->getArray(
            <<<'SQL'
            SELECT id, code
            FROM languages
SQL
        );
        $languages = [];
        foreach ($rows as $row) {
            $languages[] = $this->getByData($row);
        }
        return $languages;
    }

    /**
     * @param array $data
     * @return Language
     */
    protected function createByData(array $data)
    {
        $this->mandatory($data, ['id', 'code']);
        return new Language($data['id'], $data['code']);
    }
}
