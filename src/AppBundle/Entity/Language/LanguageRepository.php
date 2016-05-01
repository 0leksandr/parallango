<?php

namespace AppBundle\Entity\Language;

use AppBundle\Entity\AbstractSqlRepository;

/**
 * @method Language getById($id)
 * @method Language[] getByIds(array $ids)
 */
class LanguageRepository extends AbstractSqlRepository
{
    /**
     * @param string $code
     * @return Language
     */
    public function get($code)
    {
        return $this->getSingleBySelectIdQuery("
            SELECT id
            FROM languages
            WHERE code = '$code'
        ");
    }

    /**
     * @return Language[]
     */
    public function getAll()
    {
        return $this->getBySelectIdsQuery(
            <<<'SQL'
            SELECT id
            FROM languages
SQL
        );
    }

    /**
     * @return Language[]
     */
    public function getActive()
    {
        return $this->getBySelectIdsQuery(
            <<<'SQL'
            SELECT id
            FROM languages
            WHERE is_active IS TRUE
SQL
        );
    }

    /**
     * @return Language[][]
     */
    public function getPairs()
    {
        $languagePairs = [];
        $activeLanguages = $this->getActive();
        foreach ($activeLanguages as $index => $language1) {
            foreach (array_slice($activeLanguages, $index + 1) as $language2) {
                $languagePairs[] = [$language1, $language2];
            }
        }
        return $languagePairs;
    }

    /**
     * @param array $data
     * @return Language
     */
    protected function createByData(array $data)
    {
        $this->mandatory($data, ['id', 'code', 'is_active']);
        $row = $this->getRowFromArray($data);
        return new Language($row['id'], $row['code'], $row['is_active']);
    }

    /**
     * @return string
     */
    protected function getDataByIdsQuery()
    {
        return <<<'SQL'
            SELECT id, code, is_active
            FROM languages
            WHERE id IN :ids
SQL;
    }
}
