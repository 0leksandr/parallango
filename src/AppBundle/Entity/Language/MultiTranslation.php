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
    public function addValue(Language $language, $text)
    {
        $this->texts[$language->getCode()] = $text;
        return $this;
    }

    /**
     * @param Text[] $texts
     * @return MultiTranslation
     */
    public static function fromTexts(array $texts)
    {
        $self = new self();
        foreach ($texts as $text) {
            $self->addValue($text->getLanguage(), $text->getText());
        }
        return $self;
    }
}
