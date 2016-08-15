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
     * @param int|null $limit
     * @param int $offset
     * @return Section[]
     */
    public function getAll($limit = null, $offset = 0)
    {
        return $this->getBySelectIdsQuery(
            <<<'SQL'
            SELECT id
            FROM sections
SQL
            ,
            [
                'LIMIT' => $limit,
                'offset' => $offset,
            ]
        );
    }

    /**
     * @param array $data
     * @return Section
     */
    protected function createByData(array $data)
    {
        $this->mandatory($data, ['id', 'language_id', 'title', 'nr_books']);
        $row = $this->getRowFromArray($data);
        $section = new Section($row['id']);
        foreach ($row['language_id'] as $key => $languageId) {
            $language = $this->languageRepository->getById($languageId);
            $title = $row['title'][$key];
            $section->addTitle($language, $title);
        }
        $section->setNrBooks($row['nr_books']);

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
                GROUP_CONCAT(st.language_id SEPARATOR '::')
                    AS language_id__INT,
                GROUP_CONCAT(st.title SEPARATOR '::')
                    AS title__TEXT,
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
            GROUP BY s.id
            ORDER BY mnbs.nr_books DESC
            LIMIT :LIMIT OFFSET :offset
SQL;
    }
}
