<?php

namespace AppBundle\Entity\Parallango;

use AppBundle\Entity\Author\Author;
use AppBundle\Entity\Book\Book;
use AppBundle\Entity\Identifiable;
use AppBundle\Entity\Language\Language;
use AppBundle\Entity\Section\Section;
use Exception;

class Parallango extends Identifiable
{
    /** @var Book */
    private $left;
    /** @var Book */
    private $right;

    /**
     * @param int $id
     * @param Book $left
     * @param Book $right
     */
    public function __construct($id, Book $left, Book $right)
    {
        parent::__construct($id);
        $this->left = $left;
        $this->right = $right;

        $this->checkAuthor();
    }

    /**
     * @return string
     */
    public function getLeftTitle()
    {
        return $this->left->getTitle();
    }

    /**
     * @return string
     */
    public function getRightTitle()
    {
        return $this->right->getTitle();
    }

    /**
     * @return Language
     */
    public function getLeftLanguage()
    {
        return $this->left->getLanguage();
    }

    /**
     * @return Language
     */
    public function getRightLanguage()
    {
        return $this->right->getLanguage();
    }

    /**
     * @return Author
     */
    public function getAuthor()
    {
        return $this->left->getAuthor();
    }

    /**
     * @return Section|null
     */
    public function getSection()
    {
        return $this->left->getSection();
    }

    /**
     * @throws Exception
     */
    private function checkAuthor()
    {
        $leftAuthorId = $this->left->getAuthor()->getId();
        $rightAuthorId = $this->right->getAuthor()->getId();
        if ($leftAuthorId !== $rightAuthorId) {
            throw new Exception(sprintf(
                '%s %d: authors %d !== %d',
                get_class($this),
                $this->getId(),
                $leftAuthorId,
                $rightAuthorId
            ));
        }
    }
}
