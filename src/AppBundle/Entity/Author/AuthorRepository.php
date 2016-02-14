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
            $author->set(
                $row['property_name'],
                $this->languageRepository->getById($row['language_id']),
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
                alp.property_name,
                alps.language_id,
                alps.property_value
            FROM
                authors a
                JOIN author_language_property alp
                JOIN languages l
                JOIN author_language_properties alps
                    ON a.id = alps.author_id
                    AND alp.id = alps.property_id
                    AND l.id = alps.language_id
            WHERE
                a.id IN (:ids)
SQL;
    }
}
