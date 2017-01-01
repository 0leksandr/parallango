<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Author\Author;
use Symfony\Component\HttpFoundation\Request;

class AuthorController extends PageController
{
    /** @var Author */
    private $author;

    /**
     * @return string
     */
    protected function getViewName()
    {
        return 'books';
    }

    /**
     * @return array
     */
    protected function getParameters()
    {
        $parallangos = $this->get('parallango')->getByAuthor($this->author);
        return ['parallangos_list' => $this->getItemsList($parallangos)];
    }

    /**
     * @return string
     */
    protected function getPageTitle()
    {
        return $this->getTranslator()->trans(
            'author-page-title',
            ['%1%' => $this->author->getName($this->getLanguage())] // TODO: make it right
        );
    }

    /**
     * @return string[]
     */
    protected function getKeywords()
    {
        return array_merge(explode(
            ',',
            $this->getTranslator()->trans(
                'author-page-keywords',
                ['%1%' => $this->author->getName($this->getLanguage())]
            )
        ), explode(
            ',',
            $this->getTranslator()->trans('default-page-keywords')
        ));
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

    /**
     * @return string[]
     */
    protected function getJavaScripts()
    {
        return ['list'];
    }

    /**
     * @param Request $request
     */
    protected function initialize(Request $request)
    {
        parent::initialize($request);
        $this->author = $this
            ->get('author')
            ->getById($request->get('authorId'));
    }
}
