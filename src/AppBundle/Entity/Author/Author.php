<?php

namespace AppBundle\Entity\Author;

use AppBundle\Entity\Identifiable;
use AppBundle\Entity\Language\Language;
use AppBundle\Entity\Language\MultiTranslation;

class Author extends Identifiable
{
    /** @var MultiTranslation */
    private $firstName;

    /**
     * @param Language $language
     * @return string
     */
    public function getFirstName(Language $language)
    {
        return $this->firstName->getValue($language);
    }
}
