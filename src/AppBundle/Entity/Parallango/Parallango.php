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
    const ENTITY_TYPE = 'parallango';

    /** @var Book */
    private $left;
    /** @var Book */
    private $right;
    /** @var int|null */
    private $nrPages;

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
     * @param Language $language
     * @return string
     */
    public function getTitle(Language $language)
    {
        return $this->isLeftSide($language)
            ? $this->left->getTitle()
            : $this->right->getTitle();
    }

    /**
     * @param int $nrPages
     * @return $this
     */
    public function setNrPages($nrPages)
    {
        $this->nrPages = $nrPages;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getNrPages()
    {
        return $this->nrPages;
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

    /**
     * @param Language $language
     * @return bool
     * @throws Exception
     */
    private function isLeftSide(Language $language)
    {
        if ($this->left->getLanguage()->equals($language)) {
            return true;
        } elseif ($this->right->getLanguage()->equals($language)) {
            return false;
        }
        throw new Exception(sprintf(
            'Can not match language %s to book#%d',
            $language->getCode(),
            $this->getId()
        ));
    }
}
