<?php

namespace AppBundle\Entity\Page;

class Page
{
    /** @var string */
    private $text;
    /** @var int */
    private $firstParagraph;
    /** @var int */
    private $lastParagraph;

    /**
     * @param $text
     * @param int $firstParagraph
     * @param int $lastParagraph
     */
    public function __construct($text, $firstParagraph, $lastParagraph)
    {
        $this->text = $text;
        $this->firstParagraph = $firstParagraph;
        $this->lastParagraph = $lastParagraph;
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
     * @return string
     */
    public function __toString()
    {
        return $this->getText();
    }
}
