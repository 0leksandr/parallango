<?php

namespace AppBundle\Entity\Language;

class Text
{
    /** @var Language */
    private $language;
    /** @var string */
    private $text;

    /**
     * @param Language $language
     * @param string $text
     */
    public function __construct(Language $language, $text)
    {
        $this->language = $language;
        $this->text = $text;
    }

    /**
     * @return Language
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }
}
