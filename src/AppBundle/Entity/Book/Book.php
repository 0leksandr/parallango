<?php

namespace AppBundle\Entity\Book;

use AppBundle\Entity\Author\Author;
use AppBundle\Entity\Identifiable;
use AppBundle\Entity\Language\Language;
use AppBundle\Entity\Paragraph\Paragraph;
use AppBundle\Entity\Section\Section;

class Book extends Identifiable
{
    /** @var string */
    private $title;
    /** @var Author */
    private $author;
    /** @var Language */
    private $language;
    /** @var Section */
    private $section;
    /** @var Paragraph[] */
    private $paragraphs = [];

    /**
     * @param int $id
     * @param Author $author
     * @param Language $language
     * @param Section $section
     * @param string $title
     */
    public function __construct(
        $id,
        Author $author,
        Language $language,
        Section $section,
        $title
    ) {
        parent::__construct($id);
        $this->author = $author;
        $this->language = $language;
        $this->section = $section;
        $this->title = $title;
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
     * @return Section
     */
    public function getSection()
    {
        return $this->section;
    }

    /**
     * @return Paragraph[]
     */
    public function getParagraphs()
    {
        return $this->paragraphs;
    }
}
