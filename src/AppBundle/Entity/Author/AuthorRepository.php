<?php

namespace AppBundle\Entity\Author;

use AppBundle\Entity\AbstractSqlRepository;
use AppBundle\Entity\Language\Language;
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
     * @param Language $language1
     * @param Language $language2
     * @return Author[]
     */
    public function getByLanguages(Language $language1, Language $language2)
    {
        // TODO: check
        return $this->getBySelectIdsQuery(
            <<<'SQL'
            SELECT DISTINCT a.id
            FROM
                authors a
                JOIN books b1
                    ON b1.author_id = a.id
                JOIN books b2
                    ON b2.author_id = a.id
                JOIN parallangos p
                    ON b2.id = p.left_book_id
                    AND b2.id = p.right_book_id
            WHERE
                (
                    b1.language_id = :language_id_1
                    AND b2.language_id = :language_id_2
                ) OR (
                    b2.language_id = :language_id_1
                    AND b1.language_id = :language_id_2
                )
SQL
            ,
            [
                'language_id_1' => $language1->getId(),
                'language_id_2' => $language2->getId(),
            ]
        );
    }

    /**
     * @param array $data
     * @return Author
     */
    protected function createByData(array $data)
    {
        $this->mandatory(
            $data,
            ['id', 'property_name', 'language_id', 'property_value', 'nr_books']
        );
        $author = new Author($this->getIdFromMultipleRows($data));
        foreach ($data as $row) {
            $author->set(
                $row['property_name'],
                $this->languageRepository->getById($row['language_id']),
                $row['property_value']
            );
        }
        $author->setNrBooks($this->getValueFromMultipleRows($data, 'nr_books'));

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
                alps.property_value,
                COUNT(DISTINCT p.id) + COUNT(DISTINCT g.id) AS nr_books
            FROM
                authors a
                JOIN author_language_property alp
                JOIN languages l
                JOIN author_language_properties alps
                    ON a.id = alps.author_id
                    AND alp.id = alps.property_id
                    AND l.id = alps.language_id
                LEFT JOIN books b1
                    ON b1.author_id = a.id
                    AND b1.group_id IS NULL
                LEFT JOIN books b2
                    ON b2.author_id = a.id
                    AND b2.group_id IS NOT NULL
                LEFT JOIN parallangos p
                    ON p.left_book_id = b1.id
                    OR p.right_book_id = b1.id
                LEFT JOIN groups g
                    ON g.id = b2.group_id
            WHERE
                a.id IN :ids
SQL;
    }
}
