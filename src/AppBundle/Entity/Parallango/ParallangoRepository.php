<?php

namespace AppBundle\Entity\Parallango;

use AppBundle\Entity\AbstractSqlRepository;
use AppBundle\Entity\Author\Author;
use AppBundle\Entity\Book\BookRepository;
use Utils\DB\SQL;

/**
 * @method Parallango getById($id)
 */
class ParallangoRepository extends AbstractSqlRepository
{
    /** @var BookRepository */
    private $bookRepository;

    /**
     * @param SQL $sql
     * @param BookRepository $bookRepository
     */
    public function __construct(SQL $sql, BookRepository $bookRepository)
    {
        parent::__construct($sql);
        $this->bookRepository = $bookRepository;
    }

    /**
     * @param int|null $limit
     * @param int $offset
     * @return Parallango[]
     */
    public function getAll($limit = null, $offset = 0)
    {
        return $this->getBySelectIdsQuery(
            <<<'SQL'
            SELECT id
            FROM parallangos
SQL
            ,
            [
                'LIMIT' => $limit,
                'offset' => $offset,
            ]
        );
    }

    /**
     * @return Parallango
     */
    public function getRandom()
    {
        // TODO: SQL, that supports LIMIT in sub-queries!
        $id = $this->sql->getSingle(
            <<<'SQL'
            SELECT id
            FROM parallangos
            LIMIT 1 OFFSET :random
SQL
            ,
            ['random' => rand(1, $this->sql->getSingle(
                <<<'SQL'
                SELECT COUNT(*)
                FROM parallangos
SQL
            )) - 1]
        );

        return $this->getSingleBySelectIdQuery(
            <<<'SQL'
            SELECT :id
SQL
            ,
            ['id' => $id]
        );
    }

    /**
     * @param Author $author
     * @return Parallango[]
     */
    public function getByAuthor(Author $author)
    {
        return $this->getBySelectIdsQuery(
            <<<'SQL'
            SELECT id
            FROM parallangos
            WHERE
                left_book_id IN (
                    SELECT id
                    FROM books
                    WHERE author_id = :author_id
                )
#                 AND right_book_id IN (
#                     SELECT id
#                     FROM books
#                     WHERE author_id = :author_id
#                 )
SQL
            ,
            ['author_id' => $author->getId()]
        );
    }

    /**
     * @param array $data
     * @return Parallango
     */
    protected function createByData(array $data)
    {
        $this->mandatory($data, ['id', 'left_book_id', 'right_book_id']);
        $row = $this->getRowFromArray($data);
        $left = $this->bookRepository->getById($row['left_book_id']);
        $right = $this->bookRepository->getById($row['right_book_id']);

        return new Parallango($row['id'], $left, $right);
    }

    /**
     * @return string
     */
    protected function getDataByIdsQuery()
    {
        return <<<'SQL'
            SELECT
                id,
                left_book_id,
                right_book_id
            FROM parallangos
            WHERE id IN :ids
            LIMIT :LIMIT OFFSET :offset
SQL;
    }
}
