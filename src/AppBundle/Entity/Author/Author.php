<?php

namespace AppBundle\Entity\Author;

use AppBundle\Entity\Identifiable;
use AppBundle\Entity\Language\Language;
use AppBundle\Entity\Language\Translatable;

class Author extends Identifiable
{
    /** @var Translatable */
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
