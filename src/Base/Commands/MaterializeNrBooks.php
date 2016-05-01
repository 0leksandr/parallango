<?php

namespace Base\Commands;

use AppBundle\Entity\Language\Language;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Utils\DB\SQL;
use Utils\DB\ValuesList;
use Utils\ServiceContainer;

require_once __DIR__ . '/../../Utils/Utils.php';

class MaterializeNrBooks extends Command
{
    /** @var ContainerInterface */
    private $serviceContainer;
    /** @var SQL */
    private $sql;

    public function configure()
    {
        $this
            ->setName('materialize:nrbooks')
            ->setDescription('Set nr books to authors and sections');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->serviceContainer = ServiceContainer::get('prod');
        $this->sql = $this->serviceContainer->get('sql');
        $this->clearTables();

        $languagePairs = $this->getLanguagePairs();
        // TODO: languages in different order (1, 3) vs (3, 1) ?
        foreach ($languagePairs as $languagePair) {
            list($language1, $language2) = $languagePair;
            $this->processLanguagePair($language1, $language2);
        }

        return 0;
    }

    private function clearTables()
    {
        $this->sql->execute(
            <<<'SQL'
            TRUNCATE TABLE mat_nr_books_authors
SQL
        );
        $this->sql->execute(
            <<<'SQL'
            TRUNCATE TABLE mat_nr_books_sections
SQL
        );
    }

    /**
     * @return Language[][]
     */
    private function getLanguagePairs()
    {
        $languagePairs = [];
        $activeLanguages = $this
            ->serviceContainer
            ->get('language')
            ->getActive();
        foreach ($activeLanguages as $index => $language1) {
            foreach (array_slice($activeLanguages, $index + 1) as $language2) {
                $languagePairs[] = [$language1, $language2];
            }
        }
        return $languagePairs;
    }

    /**
     * TODO: get by BooksRepo method ?
     * @param Language $language1
     * @param Language $language2
     * @return int[][]
     */
    private function getBookIdsByLanguages(
        Language $language1,
        Language $language2
    ) {
        return transpose($this->sql->getArray(
            <<<'SQL'
                SELECT
                    b1.id AS id1,
                    b2.id AS id2
                FROM
                    parallangos p
                    JOIN books b1
                        ON p.left_book_id = b1.id
                    JOIN books b2
                        ON p.right_book_id = b2.id
                WHERE
                    b1.language_id = :language1_id
                    AND b2.language_id = :language2_id
SQL
            ,
            [
                'language1_id' => $language1->getId(),
                'language2_id' => $language2->getId(),
            ]
        ));
    }

    /**
     * @param Language $language1
     * @param Language $language2
     * @throws Exception
     */
    private function processLanguagePair(
        Language $language1,
        Language $language2
    ) {
        $books2Langs = $this->getBookIdsByLanguages($language1, $language2);
        $authorsNrBooks = [];
        $sectionsNrBooks = [];
        foreach ($books2Langs as $langIndex => $bookIds) {
            $booksInUberGroups = $this->getBooksInUberGroups($bookIds);
            $booksAuthors = $this->getBooksAuthors($bookIds);
            $booksSections = $this->getBooksSections($bookIds);
            $authorsNrBooks[$langIndex] = $this->getNrsBooks(
                $booksInUberGroups,
                $booksAuthors
            );
            $sectionsNrBooks[$langIndex] = $this->getNrsBooks(
                $booksInUberGroups,
                $booksSections
            );
            ksort($authorsNrBooks[$langIndex]);
            ksort($sectionsNrBooks[$langIndex]);
        }

        $this->saveNrsBooks(
            $language1,
            $language2,
            $authorsNrBooks,
            $sectionsNrBooks
        );
    }

    /**
     * @param int[] $booksIds
     * @return int[][]
     */
    private function getBooksInUberGroups(array $booksIds)
    {
        return array_merge(
            $this->getGroupsBooks($booksIds),
            array_map(function ($bookId) {
                return [$bookId];
            }, $this->getBooksNotInGroups($booksIds))
        );
    }

    /**
     * @param int[] $booksIds
     * @return int[][] [$groupId => [$bookId]]
     */
    private function getGroupsBooks(array $booksIds)
    {
        return array_reduce($this->sql->getArray(
            <<<'SQL'
            SELECT
                group_id,
                GROUP_CONCAT(id) as book_ids
            FROM books
            WHERE
                group_id IS NOT NULL
                AND id IN :book_ids
            GROUP BY group_id
SQL
            ,
            ['book_ids' => $booksIds]
        ), function ($result, $row) {
            $result[$row['group_id']] = array_map(
                'intval',
                explode(',', $row['book_ids'])
            );
            return $result;
        });
    }

    /**
     * @param int[] $booksIds
     * @return int[]
     */
    private function getBooksNotInGroups(array $booksIds)
    {
        return $this->sql->getColumn(
            <<<'SQL'
            SELECT id
            FROM books
            WHERE
                group_id IS NULL
                AND id IN :book_ids
SQL
            ,
            ['book_ids' => $booksIds]
        );
    }

    /**
     * @param int[] $bookIds
     * @return int[] [$bookId => $authorId]
     */
    private function getBooksAuthors(array $bookIds)
    {
        return array_reduce($this->sql->getArray(
            <<<'SQL'
            SELECT
                id,
                author_id
            FROM books
            WHERE id IN :ids
SQL
            ,
            ['ids' => $bookIds]
        ), function ($result, $row) {
            $result[$row['id']] = $row['author_id'];
            return $result;
        });
    }

    /**
     * @param int[] $bookIds
     * @return int[] [$bookId => $sectionId]
     */
    private function getBooksSections(array $bookIds)
    {
        return array_reduce($this->sql->getArray(
            <<<'SQL'
            SELECT
                id,
                section_id
            FROM books
            WHERE id IN :ids
SQL
            ,
            ['ids' => $bookIds]
        ), function ($result, $row) {
            $result[$row['id']] = $row['section_id'];
            return $result;
        });
    }

    /**
     * @param int[][] $booksUberGroups
     * @param int[] $groupingBooks $groupingId => $bookId
     * @return int[] $groupingId => $nrBooks
     */
    private function getNrsBooks(array $booksUberGroups, array $groupingBooks)
    {
        $nrsBooks = [];
        foreach ($booksUberGroups as $books) {
            $groupingId = $groupingBooks[head($books)];
            $nrBooks = &$nrsBooks[$groupingId];
            $nrBooks++;
        }

        return $nrsBooks;
    }

    /**
     * @param Language $language1
     * @param Language $language2
     * @param int[][] $authorsNrBooks
     * @param int[][] $sectionsNrBooks
     * @throws Exception
     */
    private function saveNrsBooks(
        Language $language1,
        Language $language2,
        array $authorsNrBooks,
        array $sectionsNrBooks
    ) {
        if ($authorsNrBooks[0] != $authorsNrBooks[1]) {
            throw new Exception(sprintf(
                'Authors are not equal. Diff: %s',
                print_r(array_diff(
                    $authorsNrBooks[0], $authorsNrBooks[1]
                ), true)
            ));
        }
        $this->saveAuthorsNrBooks(
            $authorsNrBooks[0],
            $language1,
            $language2
        );
        $this->saveSectionsNrBooks(
            $sectionsNrBooks[0],
            $language1,
            $language2
        );
    }

    /**
     * @param int[] $authorsNrBooks
     * @param Language $language1
     * @param Language $language2
     */
    private function saveAuthorsNrBooks(
        array $authorsNrBooks,
        Language $language1,
        Language $language2
    ) {
        $this->sql->execute(
            <<<'SQL'
            INSERT INTO mat_nr_books_authors (
                author_id,
                language1_id,
                language2_id,
                nr_books
            ) VALUES :values
SQL
            ,
            [
                'values' => new ValuesList(
                    array_map(
                        function ($authorId, $nrBooks)
                        use ($language1, $language2) {
                            return [
                                $authorId,
                                $language1->getId(),
                                $language2->getId(),
                                $nrBooks,
                            ];
                        },
                        array_keys($authorsNrBooks),
                        $authorsNrBooks
                    )
                ),
            ]
        );
    }

    /**
     * @param int[] $sectionsNrBooks
     * @param Language $language1
     * @param Language $language2
     */
    private function saveSectionsNrBooks(
        array $sectionsNrBooks,
        Language $language1,
        Language $language2
    ) {
        $this->sql->execute(
            <<<'SQL'
            INSERT INTO mat_nr_books_sections (
                section_id,
                language1_id,
                language2_id,
                nr_books
            ) VALUES :values
SQL
            ,
            [
                'values' => new ValuesList(
                    array_map(
                        function ($sectionId, $nrBooks)
                        use ($language1, $language2) {
                            return [
                                $sectionId,
                                $language1->getId(),
                                $language2->getId(),
                                $nrBooks,
                            ];
                        },
                        array_keys($sectionsNrBooks),
                        $sectionsNrBooks
                    )
                ),
            ]
        );
    }
}
