<?php

namespace AppBundle\Entity\Author;

use AppBundle\Entity\AbstractSqlRepository;
use AppBundle\Entity\Language\LanguageRepository;
use Utils\DB\SQL;

/**
 * @method Author getById($id)
 */
class AuthorRepository extends AbstractSqlRepository
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
     * @param array $data
     * @return Author
     */
    protected function createByData(array $data)
    {
        $author = new Author($this->getIdFromMultipleRows($data));
        foreach ($data as $row) {
            $language =
                $this->languageRepository->getByCode($row['language_code']);
            $author->set(
                $row['property_name'],
                $language,
                $row['property_value']
            );
        }
        return $author;
    }

    /**
     * @return string
     */
    protected function getDataByIdsQuery()
    {
        return <<<'SQL'
            SELECT
                a.id,
                alp1.property_name,
                l.code AS language_code,
                alp2.property_value
            FROM
                authors a
                JOIN author_language_property alp1
                JOIN languages l
                JOIN author_language_properties alp2
                    ON a.id = alp2.author_id
                    AND alp1.id = alp2.property_id
                    AND l.id = alp2.language_id
            WHERE
                a.id IN (:ids)
SQL;
    }
}
