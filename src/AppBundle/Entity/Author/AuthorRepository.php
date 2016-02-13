<?php

namespace AppBundle\Entity\Author;

use AppBundle\Entity\AbstractRepository;
use AppBundle\Entity\Language\LanguageRepository;
use Utils\DB\SQL;

require_once __DIR__ . '/../../../Utils/Utils.php';

class AuthorRepository extends AbstractRepository
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
     * @param int[] $ids
     * @return Author[]
     */
    public function getByIds(array $ids)
    {
        $data = $this->sql->getArray(
            <<<'SQL'
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
            WHERE a.id IN (:ids)
SQL
            ,
            ['ids' => $ids]
        );
        $authors = [];
        $grouped = igroup($data, 'id');
        foreach ($grouped as $id => $data) {
            $authors[] = $this->getByIdData($id, $data);
        }
        return $authors;
    }

    /**
     * @param array $data
     * @return Author
     * @throws \Exception
     */
    protected function createByData(array $data)
    {
        $ids = array_unique(ipull($data, 'id'));
        if (count($ids) !== 1) {
            throw new \Exception(sprintf(
                'Incorrect data provided for %s object',
                Author::class
            ));
        }
        $author = new Author(head($ids));
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
}
