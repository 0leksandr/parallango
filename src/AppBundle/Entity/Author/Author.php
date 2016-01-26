<?php

namespace AppBundle\Entity\Author;

use AppBundle\Entity\Identifiable;
use AppBundle\Entity\Language\Language;
use AppBundle\Entity\Language\MultiTranslation;

class Author extends Identifiable
{
    /** @var MultiTranslation */
    private $name;

    /**
     * @param int $id
     * @param MultiTranslation $name
     */
    public function __construct($id, MultiTranslation $name)
    {
        parent::__construct($id);
        $this->name = $name;
    }

    /**
     * @param Language $language
     * @return string
     */
    public function getName(Language $language)
    {
        return $this->name->getValue($language);
    }
}
