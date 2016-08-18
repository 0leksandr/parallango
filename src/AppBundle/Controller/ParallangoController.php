<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Page\Page;
use AppBundle\Entity\Parallango\Parallango;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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
        return ['page' => $this->getPage()];
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

    /**
     * @return string[]
     */
    protected function getJavaScripts()
    {
        return ['ajax-link'];
    }

    protected function initialize(Request $request)
    {
        parent::initialize($request);
        $this->parallango = $this
            ->get('parallango')
            ->getById($request->get('parallangoId'));
        $this->pageNumber =
            (intval($request->get('pageNumber')) ?: 1) - 1;
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function ajaxAction(Request $request)
    {
        $this->initialize($request);
        return $this->render(
            '@App/book.html.twig',
            ['page' => $this->getPage()]
        );
    }

    /**
     * @return Page
     */
    private function getPage()
    {
        $pageSize = $this->get('page_size')->get(10000);
        $pages = $this
            ->get('page')
            ->getByParallangoAndPageSize($this->parallango, $pageSize); // TODO: optimize (get only requested page number instead of all pages)

        return $pages[$this->pageNumber];
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
