<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Author\Author;

class AuthorController extends PageController
{
    /** @var Author */
    private $author;

    /**
     * @return string
     */
    protected function getViewName()
    {
        return 'author';
    }

    /**
     * @return array
     */
    protected function getParameters()
    {
        return [
            'parallangos' => $this
                ->get('parallango')
                ->getByAuthor($this->author),
        ];
    }

    /**
     * @return string
     */
    protected function getPageTitle()
    {
        $this->getTranslator()->trans(
            'author-page-title',
            ['%1%' => $this->author->getName($this->getLanguage())] // TODO: make it right
        );
    }

    /**
     * @return string[]
     */
    protected function getKeywords()
    {
        return array_map('trim', array_merge(explode(
            ',',
            $this->getTranslator()->trans(
                'author-page-keywords',
                ['%1%' => $this->author->getName($this->getLanguage())]
            )
        ), explode(
            ',',
            $this->getTranslator()->trans('default-page-keywords')
        )));
    }

    /**
     * @return string
     */
    protected function getDescription()
    {
        return $this->getTranslator()->trans(
            'author-page-description',
            ['%1%' => $this->author->getName($this->getLanguage())]
        );
    }

    /**
     * @return string[]|null
     */
    protected function getRobots()
    {
        return null;
    }

    /**
     * @return array
     */
    protected function getRequestParams()
    {
        return ['authorId' => $this->author->getId()];
    }

    protected function initialize()
    {
        parent::initialize();
        $this->author = $this
            ->get('author')
            ->getById($this->getRequest()->get('authorId'));
    }
}
