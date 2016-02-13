<?php

namespace AppBundle\Entity\Section;

use AppBundle\Entity\Identifiable;
use AppBundle\Entity\Language\Language;
use AppBundle\Entity\Language\MultiTranslation;

class Section extends Identifiable
{
    /** @var MultiTranslation */
    private $title;

    /**
     * @param int $id
     */
    public function __construct($id)
    {
        parent::__construct($id);
        $this->title = new MultiTranslation();
    }

    /**
     * @param Language $language
     * @return string
     */
    public function getTitle(Language $language)
    {
        return $this->title->getValue($language);
    }

    /**
     * @param Language $language
     * @param string $title
     * @return $this
     */
    public function addTitle(Language $language, $title)
    {
        $this->title->addValue($language, $title);
        return $this;
    }
}
