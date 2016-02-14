<?php

namespace AppBundle\Entity\Book;

use AppBundle\Entity\Author\Author;
use AppBundle\Entity\Identifiable;
use AppBundle\Entity\Language\Language;
use AppBundle\Entity\Section\Section;

class Book extends Identifiable
{
    /** @var string */
    private $title;
    /** @var Author */
    private $author;
    /** @var Language */
    private $language;
    /** @var Section|null */
    private $section;

    /**
     * @param int $id
     * @param string $title
     * @param Author $author
     * @param Language $language
     * @param Section|null $section
     */
    public function __construct(
        $id,
        $title,
        Author $author,
        Language $language,
        Section $section = null
    ) {
        parent::__construct($id);
        $this->title = $title;
        $this->author = $author;
        $this->language = $language;
        $this->section = $section;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return Author
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * @return Language
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * @return Section|null
     */
    public function getSection()
    {
        return $this->section;
    }
}
