<?php

namespace AppBundle\Entity\Page;

use AppBundle\Entity\Page\Pagination\Page as PaginationPage;
use AppBundle\Entity\Parallango\Parallango;
use Exception;

class Page
{
    /** @var string */
    private $text;
    /** @var int */
    private $firstParagraph;
    /** @var int */
    private $lastParagraph;
    /** @var int */
    private $pageNumber;
    /** @var Parallango */
    private $parallango;
    /** @var PaginationPage[] */
    private $pages;

    /**
     * @param Parallango $parallango
     * @param $text
     * @param int $firstParagraph
     * @param int $lastParagraph
     * @param int $pageNumber
     */
    public function __construct(
        Parallango $parallango,
        $text,
        $firstParagraph,
        $lastParagraph,
        $pageNumber
    ) {
        $this->parallango = $parallango;
        $this->text = $text;
        $this->firstParagraph = $firstParagraph;
        $this->lastParagraph = $lastParagraph;
        $this->pageNumber = $pageNumber;
    }

    /**
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * @return int
     */
    public function getFirstParagraph()
    {
        return $this->firstParagraph;
    }

    /**
     * @return int
     */
    public function getLastParagraph()
    {
        return $this->lastParagraph;
    }

    /**
     * @return PaginationPage[]
     * @throws Exception
     */
    public function getPages()
    {
        if ($this->pages === null) {
            $nrPages = 10;

            $this->pages = [];

            if ($this->pageNumber !== 0) {
                $this->pages[] = new PaginationPage(
                    $this->parallango,
                    0,
                    '&lt;&lt;',
                    false,
                    false
                );
                $this->pages[] = new PaginationPage(
                    $this->parallango,
                    $this->pageNumber - 1,
                    '&lt;',
                    false,
                    false
                );
            }

            $maxLeft = ceil(($nrPages - 1) / 2);
            $min = 0;
            if ($this->pageNumber > $maxLeft) {
                $min = $this->pageNumber - $maxLeft;
            }
            $max = $min + $nrPages - 1;
            $totalNrPages = $this->parallango->getNrPages();
            if ($totalNrPages === null) {
                throw new Exception(sprintf(
                    'NrPages for parallango %d not set',
                    $this->parallango->getId()
                ));
            }
            $last = $totalNrPages - 1;
            if ($max > $last) {
                $max = $last;
            }
            foreach (range($min, $max) as $pageNumber) {
                $this->pages[] = new PaginationPage(
                    $this->parallango,
                    $pageNumber,
                    $pageNumber + 1,
                    $pageNumber === $this->pageNumber,
                    $pageNumber > $this->pageNumber
                );
            }

            if ($this->pageNumber !== $last) {
                $this->pages[] = new PaginationPage(
                    $this->parallango,
                    $this->pageNumber + 1,
                    '&gt;',
                    false,
                    true
                );
                $this->pages[] = new PaginationPage(
                    $this->parallango,
                    $last,
                    '&gt;&gt;',
                    false,
                    true
                );
            }
        }

        return $this->pages;
    }
}
