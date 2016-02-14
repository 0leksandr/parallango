<?php

namespace AppBundle\Entity\Book;

use AppBundle\Entity\AbstractSqlRepository;
use AppBundle\Entity\Author\AuthorRepository;
use AppBundle\Entity\Language\LanguageRepository;
use AppBundle\Entity\Section\SectionRepository;
use Utils\DB\SQL;

class BooksRepository extends AbstractSqlRepository
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
     * @param array $data
     * @return Book
     */
    protected function createByData(array $data)
    {
        $this->mandatory(
            $data,
            ['id', 'author_id', 'language_id', 'section_id', 'title']
        );
        $row = $this->getRowFromArray($data);

        return new Book(
            $row['id'],
            $this->authorRepository->getById($row['author_id']),
            $this->languageRepository->getById($row['language_id']),
            $this->sectionRepository->getById($row['section_id']),
            $row['title']
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
