<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Author\Author;
use AppBundle\Entity\ItemsList\ListItem;
use AppBundle\Entity\Language\Language;
use AppBundle\Entity\Parallango\Parallango;
use AppBundle\Entity\Section\Section;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Bundle\FrameworkBundle\Routing\Router;

class ToListItemConvertibleController extends Controller
{
    /**
     * @param Author[] $authors
     * @return ListItem[]
     */
    protected function authorsToListItems(array $authors)
    {
        return array_map(function (Author $author) {
            return new ListItem(
                $this->getRouter()->generate(
                    'author',
                    ['authorId' => $author->getId()]
                ),
                $author->getName($this->getLanguage()),
                $author->getNrBooks()
            );
        }, $authors);
    }

    /**
     * @param Section[] $sections
     * @return ListItem[]
     */
    protected function sectionsToListItems(array $sections)
    {
        return array_map(function (Section $section) {
            return new ListItem(
                $this->getRouter()->generate(
                    'section',
                    ['sectionId' => $section->getId()]
                ),
                $section->getTitle($this->getLanguage()),
                $section->getNrBooks()
            );
        }, $sections);
    }

    /**
     * @param Parallango[] $parallangos
     * @return ListItem[]
     */
    protected function parallangosToListItems(array $parallangos)
    {
        return array_map(function (Parallango $parallango) {
            return new ListItem(
                $this->getRouter()->generate(
                    'parallango',
                    ['parallangoId' => $parallango->getId()]
                ),
                $parallango->getTitle($this->getLanguage()),
                $parallango->getAuthor()->getName($this->getLanguage())
            );
        }, $parallangos);
    }

    /**
     * @return Router
     */
    private function getRouter()
    {
        return $this->get('router');
    }

    /**
     * @return Language
     */
    private function getLanguage()
    {
        return $this->get('language.current');
    }
}
