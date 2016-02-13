<?php

namespace AppBundle\Entity\Section;

use AppBundle\Entity\AbstractRepository;
use AppBundle\Entity\Language\LanguageRepository;
use Utils\DB\SQL;

require_once __DIR__ . '/../../../Utils/Utils.php';

class SectionRepository extends AbstractRepository
{
    /** @var SQL */
    private $sql;
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
        $this->sql = $sql;
        $this->languageRepository = $languageRepository;
    }

    /**
     * @return Section[]
     */
    public function getAll()
    {
        $data = $this->sql->getArray(
            <<<'SQL'
            SELECT
                s.id,
                st.language_id,
                st.title
            FROM
                sections s
                JOIN section_titles st
                    ON s.id = st.section_id
SQL
        );
        $sections = [];
        foreach(igroup($data, 'id') as $id => $data) {
            $sections[] = $this->getByIdData($id, $data);
        }
        return $sections;
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
}
