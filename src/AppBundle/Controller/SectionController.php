<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Section\Section;

class SectionController extends PageController
{
    /** @var Section */
    private $section;

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
        return [
            'parallangos' => $this
                ->get('parallango')
                ->getBySection($this->section),
        ];
    }

    /**
     * @return string
     */
    protected function getPageTitle()
    {
        return $this->getTranslator()->trans('section-page-title');
    }

    /**
     * @return string[]
     */
    protected function getKeywords()
    {
        return array_merge(explode(
            ',',
            $this->getTranslator()->trans(
                'section-page-keywords',
                ['%1%' => $this->section->getTitle($this->getLanguage())]
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
        return $this->getTranslator()->trans('section-page-description');
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
        return ['sectionId' => $this->section->getId()];
    }

    protected function initialize()
    {
        parent::initialize();
        $this->section = $this
            ->get('section')
            ->getById($this->getRequest()->get('sectionId'));
    }
}
