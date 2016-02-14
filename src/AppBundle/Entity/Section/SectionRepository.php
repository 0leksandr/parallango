<?php

namespace AppBundle\Entity\Section;

use AppBundle\Entity\AbstractSqlRepository;
use AppBundle\Entity\Language\LanguageRepository;
use Utils\DB\SQL;

/**
 * @method Section getById($id)
 */
class SectionRepository extends AbstractSqlRepository
{
    /** @var LanguageRepository */
    private $languageRepository;

    /**
     * @param SQL $sql
     * @param LanguageRepository $languageRepository
     */
    public function __construct(
        SQL $sql,
        LanguageRepository $languageRepository
    ) {
        parent::__construct($sql);
        $this->languageRepository = $languageRepository;
    }

    /**
     * @return Section[]
     */
    public function getAll()
    {
        return $this->getBySelectIdsQuery(
            <<<'SQL'
            SELECT id
            FROM sections
SQL
        );
    }

    /**
     * @param array $data
     * @return Section
     */
    protected function createByData(array $data)
    {
        $this->mandatory($data, ['id', 'language_id', 'title']);
        $section = new Section($this->getIdFromMultipleRows($data));
        foreach ($data as $row) {
            $language = $this->languageRepository->getById($row['language_id']);
            $section->addTitle($language, $row['title']);
        }
        return $section;
    }

    /**
     * @return string
     */
    protected function getDataByIdsQuery()
    {
        return <<<'SQL'
            SELECT
                s.id,
                st.language_id,
                st.title
            FROM
                sections s
                LEFT JOIN section_titles st
                    ON s.id = st.section_id
            WHERE
                s.id IN (:ids)
SQL;
    }
}
