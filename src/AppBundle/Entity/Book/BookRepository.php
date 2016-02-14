<?php

namespace AppBundle\Entity\Book;

use AppBundle\Entity\AbstractSqlRepository;
use AppBundle\Entity\Author\AuthorRepository;
use AppBundle\Entity\Language\LanguageRepository;
use AppBundle\Entity\Section\SectionRepository;
use Utils\DB\SQL;

/**
 * @method Book getById($id)
 */
class BookRepository extends AbstractSqlRepository
{
    /** @var AuthorRepository */
    private $authorRepository;
    /** @var LanguageRepository */
    private $languageRepository;
    /** @var SectionRepository */
    private $sectionRepository;

    /**
     * @param SQL $sql
     * @param AuthorRepository $authorRepository
     * @param LanguageRepository $languageRepository
     * @param SectionRepository $sectionRepository
     */
    public function __construct(
        SQL $sql,
        AuthorRepository $authorRepository,
        LanguageRepository $languageRepository,
        SectionRepository $sectionRepository
    ) {
        parent::__construct($sql);
        $this->authorRepository = $authorRepository;
        $this->languageRepository = $languageRepository;
        $this->sectionRepository = $sectionRepository;
    }

    /**
     * @return Book[]
     */
    public function getAll()
    {
        return $this->getBySelectIdsQuery(
            <<<'SQL'
            SELECT id
            FROM books
SQL
        );
    }

    /**
     * @param array $data
     * @return Book
     */
    protected function createByData(array $data)
    {
        $this->mandatory($data, ['id', 'author_id', 'language_id', 'title']);
        $row = $this->getRowFromArray($data);
        if (($sectionId = $row['section_id']) !== null) {
            $section = $this->sectionRepository->getById($sectionId);
        } else {
            $section = null;
        }

        return new Book(
            $row['id'],
            $row['title'],
            $this->authorRepository->getById($row['author_id']),
            $this->languageRepository->getById($row['language_id']),
            $section
        );
    }

    /**
     * @return string
     */
    protected function getDataByIdsQuery()
    {
        return <<<'SQL'
            SELECT
                id,
                author_id,
                language_id,
                section_id,
                title
            FROM books
            WHERE id IN (:ids)
SQL;
    }
}
