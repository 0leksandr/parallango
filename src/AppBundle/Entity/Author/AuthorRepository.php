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
     * @return Author[]
     */
    public function getAll()
    {
        return $this->getBySelectIdsQuery(
            <<<'SQL'
            SELECT id
            FROM authors
SQL
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
                mnba.nr_books
            FROM
                authors a
                JOIN author_language_property alp
                JOIN languages l
                JOIN author_language_properties alps
                    ON a.id = alps.author_id
                    AND alp.id = alps.property_id
                    AND l.id = alps.language_id
                LEFT JOIN mat_nr_books_authors mnba
                    ON mnba.author_id = a.id
                    #TODO: make it work with all language pairs
                    AND mnba.language1_id = 1
                    AND mnba.language2_id = 3
            WHERE a.id IN :ids
            ORDER BY mnba.nr_books
SQL;
    }
}
