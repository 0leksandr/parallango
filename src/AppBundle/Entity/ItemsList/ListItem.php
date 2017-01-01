<?php

namespace AppBundle\Entity\ItemsList;

class ListItem
{
    /** @var string */
    private $url;
    /** @var string */
    private $text;
    /** @var string|null */
    private $additional;

    /**
     * @param string $url
     * @param string $text
     * @param string|null $additional
     */
    public function __construct($url, $text, $additional)
    {
        $this->url = $url;
        $this->text = $text;
        $this->additional = $additional;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * @return string|null
     */
    public function getAdditional()
    {
        return $this->additional;
    }
}
