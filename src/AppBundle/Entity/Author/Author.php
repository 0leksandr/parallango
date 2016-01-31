<?php

namespace AppBundle\Entity\Author;

use AppBundle\Entity\Identifiable;
use AppBundle\Entity\Language\Language;
use AppBundle\Entity\Language\MultiTranslation;

class Author extends Identifiable
{
    const PSEUDONYM = 'pseudonym';
    const FIRST_NAME = 'first name';
    const LAST_NAME = 'last name';
    const WIKI_PAGE = 'wiki page';

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
