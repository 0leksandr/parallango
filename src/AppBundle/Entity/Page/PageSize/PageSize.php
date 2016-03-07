<?php

namespace AppBundle\Entity\Page\PageSize;

use AppBundle\Entity\Identifiable;

class PageSize extends Identifiable
{
    /** @var int */
    private $pageSizeSymbols;

    /**
     * @param int $id
     * @param int $pageSizeSymbols
     */
    public function __construct($id, $pageSizeSymbols)
    {
        parent::__construct($id);
        $this->pageSizeSymbols = $pageSizeSymbols;
    }

    /**
     * @return int
     */
    public function getPageSizeSymbols()
    {
        return $this->pageSizeSymbols;
    }
}
