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
        $this->mandatory($data, ['id', 'language_id', 'title', 'nr_books']);
        $section = new Section($this->getIdFromMultipleRows($data));
        foreach ($data as $row) {
            $language = $this->languageRepository->getById($row['language_id']);
            $section->addTitle($language, $row['title']);
        }
        $section->setNrBooks(
            $this->getValueFromMultipleRows($data, 'nr_books')
        );

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
                st.title,
                COUNT(DISTINCT p.id) + COUNT(DISTINCT g.id) AS nr_books
            FROM
                sections s
                JOIN section_titles st
                    ON s.id = st.section_id
                LEFT JOIN books b1
                    ON b1.section_id = s.id
                    AND b1.group_id IS NULL
                LEFT JOIN books b2
                    ON b2.section_id = s.id
                    AND b2.group_id IS NOT NULL
                LEFT JOIN parallangos p
                    ON p.left_book_id = b1.id
                    OR p.right_book_id = b1.id
                LEFT JOIN groups g
                    ON g.id = b2.group_id
            WHERE
                s.id IN :ids
SQL;
    }
}
