<?php

namespace AppBundle\Entity\Page\Pagination;

use AppBundle\Entity\Parallango\Parallango;

class Page
{
    /** @var Parallango */
    private $parallango;
    /** @var int */
    private $number;
    /** @var string */
    private $text;
    /** @var bool */
    private $isActive;
    /** @var bool */
    private $isNext;

    /**
     * @param Parallango $parallango
     * @param int $number
     * @param string $text
     * @param bool $isActive
     * @param bool $isNext
     */
    public function __construct(
        Parallango $parallango,
        $number,
        $text,
        $isActive,
        $isNext
    ) {
        $this->parallango = $parallango;
        $this->number = $number;
        $this->text = $text;
        $this->isActive = $isActive;
        $this->isNext = $isNext;
    }

    /**
     * @return Parallango
     */
    public function getParallango()
    {
        return $this->parallango;
    }

    /**
     * @return int
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return $this->isActive;
    }

    /**
     * @return bool
     */
    public function isNext()
    {
        return $this->isNext;
    }
}
