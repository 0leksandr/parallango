<?php

namespace AppBundle\Entity\Page;

use AppBundle\Entity\Page\PageSize\PageSize;
use AppBundle\Entity\Parallango\Parallango;
use Utils\DB\SQL;

class PageRepository
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

    /**
     * @param Parallango $parallango
     * @param PageSize $pageSize
     * @return Page[]
     */
    public function getByParallangoAndPageSize(
        Parallango $parallango,
        PageSize $pageSize
    ) {
        $paragraphs = $this->sql->getArray(
            <<<'SQL'
            SELECT
                p.order,
                p.position_begin,
                p.position_end
            FROM
                paragraphs p
                JOIN materialized_pages mp
                    ON mp.paragraph_id = p.id
            WHERE
                p.parallango_id = :parallango_id
                AND mp.page_size_id = :page_size_id
            ORDER BY
                p.order
SQL
            ,
            [
                'parallango_id' => $parallango->getId(),
                'page_size_id' => $pageSize->getId(),
            ]
        );
        $pages = [];
        foreach (array_slice($paragraphs, 0, -1) as $index => $paragraphFrom) {
            $paragraphTo = $paragraphs[$index + 1];
            $pages[] = $this->create(
                $parallango,
                $paragraphFrom['order'],
                $paragraphTo['order'],
                $paragraphFrom['position_begin'],
                $paragraphTo['position_end']
            );
        }
        return $pages;
    }

    /**
     * @param Parallango $parallango
     * @param int $firstParagraph
     * @param int $lastParagraph
     * @param int $textPositionFrom
     * @param int $textPositionTo
     * @return Page
     */
    private function create(
        Parallango $parallango,
        $firstParagraph,
        $lastParagraph,
        $textPositionFrom,
        $textPositionTo
    ) {
        $filename = sprintf(
            '%s/%d.html',
            $this->booksPath,
            $parallango->getId()
        );
        $fhandle = fopen($filename, 'r');
        fseek($fhandle, $textPositionFrom);
        $text = fgets(
            $fhandle,
            $textPositionTo - $textPositionFrom + 2
        );
        fclose($fhandle);

        return new Page($text, $firstParagraph, $lastParagraph);
    }
}