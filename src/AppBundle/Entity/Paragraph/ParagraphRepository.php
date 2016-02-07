<?php

namespace AppBundle\Entity\Paragraph;

use AppBundle\Entity\Book\Book;
use AppBundle\Utils\DB\SQL;

class ParagraphRepository
{
    /** @var SQL */
    private $sql;
    /** @var string */
    private $booksPath;

    /**
     * @param SQL $sql
     * @param string $booksPath
     */
    public function __construct(SQL $sql, $booksPath)
    {
        $this->sql = $sql;
        $this->booksPath = $booksPath;
    }

    public function getByBookAndPosition(Book $book, $position, $limit = 15)
    {
        $serialized = $this->sql->getArray(
            <<<'SQL'
SQL
        );
    }
}
