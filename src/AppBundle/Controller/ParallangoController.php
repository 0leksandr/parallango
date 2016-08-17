<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Parallango\Parallango;

class ParallangoController extends PageController
{
    /** @var Parallango */
    private $parallango;
    /** @var int */
    private $pageNumber;

    /**
     * @return string
     */
    protected function getViewName()
    {
        return 'book';
    }

    /**
     * @return array
     */
    protected function getParameters()
    {
        $pageSize = $this->get('page_size')->get(10000);
        $pages = $this
            ->get('page')
            ->getByParallangoAndPageSize($this->parallango, $pageSize); // TODO: optimize (get only requested page number instead of all pages)

        return [
            'page' => $pages[$this->pageNumber],
        ];
    }

    /**
     * @return string
     */
    protected function getPageTitle()
    {
        return $this->getTempText();
    }

    /**
     * @return string[]
     */
    protected function getKeywords()
    {
        return [$this->getTempText()];
    }

    /**
     * @return string
     */
    protected function getDescription()
    {
        return $this->getTempText();
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
        return [
            'parallangoId' => $this->parallango->getId(),
            'pageNumber' => $this->pageNumber + 1,
        ];
    }

    protected function initialize()
    {
        parent::initialize();
        $this->parallango = $this
            ->get('parallango')
            ->getById($this->getRequest()->get('parallangoId'));
        $this->pageNumber =
            (intval($this->getRequest()->get('pageNumber')) ?: 1) - 1;
    }

    /**
     * @return string
     */
    private function getTempText()
    {
        return sprintf(
            '%s, %s',
            $this->parallango->getTitle($this->getLanguage()),
            $this->parallango->getAuthor()->getName($this->getLanguage())
        );
    }
}
