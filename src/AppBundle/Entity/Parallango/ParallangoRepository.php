<?php

namespace AppBundle\Entity\Parallango;

use AppBundle\Entity\AbstractSqlRepository;
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
     * @return Parallango[]
     */
    public function getAll()
    {
        return $this->getBySelectIdsQuery(
            <<<'SQL'
            SELECT id
            FROM parallangos
SQL
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
SQL;
    }
}
