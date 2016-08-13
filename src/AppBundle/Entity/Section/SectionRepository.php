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
                mnbs.nr_books
            FROM
                sections s
                JOIN section_titles st
                    ON s.id = st.section_id
                JOIN mat_nr_books_sections mnbs
                    ON s.id = mnbs.section_id
                    # TODO: same as authors
                    AND mnbs.language1_id = 1
                    AND mnbs.language2_id = 3
            WHERE s.id IN :ids
            ORDER BY mnbs.nr_books DESC
SQL;
    }
}
