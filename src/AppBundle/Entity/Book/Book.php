<?php

namespace AppBundle\Entity\Book;

use AppBundle\Entity\Author\Author;
use AppBundle\Entity\Identifiable;
use AppBundle\Entity\Language\Language;
use AppBundle\Entity\Paragraph\Paragraph;

class Book extends Identifiable
{
    /** @var string */
    private $title;
    /** @var Author */
    private $author;
    /** @var Paragraph[] */
    private $paragraphs = [];
    /** @var Language */
    private $language;

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
     * @return Paragraph[]
     */
    public function getParagraphs()
    {
        return $this->paragraphs;
    }

    /**
     * @return Language
     */
    public function getLanguage()
    {
        return $this->language;
    }
}
