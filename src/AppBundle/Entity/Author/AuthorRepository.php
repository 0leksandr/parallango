<?php

namespace AppBundle\Entity\Author;

use AppBundle\Entity\AbstractSqlRepository;
use AppBundle\Entity\Language\LanguageRepository;
use Utils\DB\SQL;

/**
 * @method Author getById($id)
 * @method Author[] getBySelectIdsQuery($query, array $params = [])
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
     * @param int|null $limit
     * @param int $offset
     * @return Author[]
     */
    public function getAll($limit = null, $offset = 0)
    {
        return $this->getBySelectIdsQuery(
            <<<'SQL'
            SELECT id
            FROM authors
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
     * @return Author
     */
    protected function createByData(array $data)
    {
        $this->mandatory(
            $data,
            ['id', 'property_name', 'language_id', 'property_value', 'nr_books']
        );

        $data = $this->getRowFromArray($data);
        $author = new Author($data['id']);
        foreach (array_keys($data['property_name']) as $key) {
            $language = $this
                ->languageRepository
                ->getById($data['language_id'][$key]);
            $author->set(
                $data['property_name'][$key],
                $language,
                $data['property_value'][$key]
            );
        }
        $author->setNrBooks($data['nr_books']);

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
                GROUP_CONCAT(alp.property_name SEPARATOR '::')
                    AS property_name__TEXT,
                GROUP_CONCAT(alps.language_id SEPARATOR '::')
                    AS language_id__INT,
                GROUP_CONCAT(alps.property_value SEPARATOR '::')
                    AS property_value__TEXT,
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
            GROUP BY a.id
            ORDER BY mnba.nr_books DESC
            LIMIT :LIMIT OFFSET :offset
SQL;
    }
}
