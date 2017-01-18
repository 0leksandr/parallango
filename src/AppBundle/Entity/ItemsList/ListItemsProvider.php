<?php

namespace AppBundle\Entity\ItemsList;

use AppBundle\Entity\Author\Author;
use AppBundle\Entity\Author\AuthorRepository;
use AppBundle\Entity\Language\Language;
use AppBundle\Entity\Parallango\Parallango;
use AppBundle\Entity\Parallango\ParallangoRepository;
use AppBundle\Entity\Section\Section;
use AppBundle\Entity\Section\SectionRepository;
use Exception;
use Symfony\Component\Routing\Router;

class ListItemsProvider
{
    const NR_ITEMS = 50;

    /** @var AuthorRepository */
    private $authorRepo;
    /** @var SectionRepository */
    private $sectionRepo;
    /** @var ParallangoRepository */
    private $parallangoRepo;
    /** @var Router */
    private $router;
    /** @var Language */
    private $language;

    public function __construct(
        AuthorRepository $authorRepo,
        SectionRepository $sectionRepo,
        ParallangoRepository $parallangoRepo,
        Router $router,
        Language $language
    ) {
        $this->authorRepo = $authorRepo;
        $this->sectionRepo = $sectionRepo;
        $this->parallangoRepo = $parallangoRepo;
        $this->router = $router;
        $this->language = $language;
    }

    /**
     * @param string $entityType
     * @param string|null $relatedEntity
     * @param int|null $relatedId
     * @param int $index
     * @return ListItem[]
     * @throws Exception
     */
    public function getListItems(
        $entityType,
        $relatedEntity = null,
        $relatedId = null,
        $index
    ) {
        $nrItems = self::NR_ITEMS;
        $offset = $index * $nrItems;
        switch ($entityType) {
            case Author::ENTITY_TYPE:
                $authors = $this->authorRepo->getAll($nrItems, $offset);
                $items = $this->authorsToListItems($authors);
                break;
            case Section::ENTITY_TYPE:
                $sections = $this->sectionRepo->getAll($nrItems, $offset);
                $items = $this->sectionsToListItems($sections);
                break;
            case Parallango::ENTITY_TYPE:
                switch ($relatedEntity) {
                    case null:
                        $parallangos = $this->parallangoRepo->getAll(
                            $nrItems,
                            $offset
                        );
                        break;
                    case Author::ENTITY_TYPE:
                        $author = $this->authorRepo->getById($relatedId);
                        $parallangos = $this->parallangoRepo->getByAuthor(
                            $author,
                            $nrItems,
                            $offset
                        );
                        break;
                    case Section::ENTITY_TYPE:
                        $section = $this->sectionRepo->getById($relatedId);
                        $parallangos = $this->parallangoRepo->getBySection(
                            $section,
                            $nrItems,
                            $offset
                        );
                        break;
                    default:
                        throw new Exception(sprintf(
                            'Can not resolve relatedEntity "%s"',
                            $relatedEntity
                        ));
                }
                $items = $this->parallangosToListItems($parallangos);
                break;
            default:
                throw new Exception(sprintf(
                    'Can not resolve elements type "%s"', $entityType
                ));
        }

        return $items;
    }

    /**
     * @param Author[] $authors
     * @return ListItem[]
     */
    private function authorsToListItems(array $authors)
    {
        return map($authors, function (Author $author) {
            return new ListItem(
                $this->router->generate(
                    'author',
                    ['authorId' => $author->getId()]
                ),
                $author->getName($this->language),
                $author->getNrBooks()
            );
        });
    }

    /**
     * @param Section[] $sections
     * @return ListItem[]
     */
    private function sectionsToListItems(array $sections)
    {
        return map($sections, function (Section $section) {
            return new ListItem(
                $this->router->generate(
                    'section',
                    ['sectionId' => $section->getId()]
                ),
                $section->getTitle($this->language),
                $section->getNrBooks()
            );
        });
    }

    /**
     * @param Parallango[] $parallangos
     * @return ListItem[]
     */
    private function parallangosToListItems(array $parallangos)
    {
        return map($parallangos, function (Parallango $parallango) {
            return new ListItem(
                $this->router->generate(
                    'parallango',
                    ['parallangoId' => $parallango->getId()]
                ),
                $parallango->getTitle($this->language),
                $parallango->getAuthor()->getName($this->language)
            );
        });
    }
}
