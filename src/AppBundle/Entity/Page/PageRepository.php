<?php

namespace AppBundle\Entity\Page;

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
     * @param int $paragraphFrom
     * @param int $paragraphTo
     * @return Page
     */
    public function getByParallangoAndParagraphs(
        Parallango $parallango,
        $paragraphFrom,
        $paragraphTo
    ) {
        $paragraphPositions = $this->sql->getColumn(
            <<<'SQL'
            SELECT position_begin
            FROM paragraphs
            WHERE
                parallango_id = :parallango_id
                AND `order` = :paragraph_from

            UNION

            SELECT position_end
            FROM paragraphs
            WHERE
                parallango_id = :parallango_id
                AND `order` = :paragraph_to
SQL
            ,
            [
                'parallango_id' => $parallango->getId(),
                'paragraph_from' => $paragraphFrom,
                'paragraph_to' => $paragraphTo,
            ]
        );
        return $this->getSingle(
            $parallango,
            $paragraphFrom,
            $paragraphTo,
            $paragraphPositions[0],
            $paragraphPositions[1]
        );
    }

    /**
     * @param Parallango $parallango
     * @param int $firstParagraph
     * @param int $lastParagraph
     * @param int $textPositionFrom
     * @param int $textPositionTo
     * @return Page
     */
    private function getSingle(
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
