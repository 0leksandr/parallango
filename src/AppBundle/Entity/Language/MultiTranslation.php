<?php

namespace AppBundle\Entity\Language;

use Symfony\Component\PropertyAccess\Exception\NoSuchIndexException;

class MultiTranslation
{
    /** @var string[] [$langCode => $name, ] */
    private $texts = [];

    /**
     * @param Language $language
     * @return string
     */
    public function getValue(Language $language)
    {
        if ($text = $this->texts[$language->getCode()]) {
            return $text;
        }
        throw new NoSuchIndexException();
    }

    /**
     * @param Language $language
     * @param string $text
     * @return $this
     */
    public function setValue(Language $language, $text)
    {
        $this->texts[$language->getCode()] = $text;
        return $this;
    }
}
