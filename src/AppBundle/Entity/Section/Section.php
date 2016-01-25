<?php

namespace AppBundle\Entity\Section;

use AppBundle\Entity\Identifiable;
use AppBundle\Entity\Language\Language;
use AppBundle\Entity\Language\Translatable;

class Section extends Identifiable
{
    /** @var Translatable */
    private $title;

    /**
     * @param Language $language
     * @return string
     */
    public function getTitle(Language $language)
    {
        return $this->title->getValue($language);
    }
}
