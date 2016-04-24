<?php

namespace Base\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Utils\DB\SQL;
use Utils\ServiceContainer;

class MaterializeNrBooks extends Command
{
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
        $serviceContainer = ServiceContainer::get('prod');
        $sql = $serviceContainer->get('sql');

        $groupsBooks = array_reduce($sql->getArray(
            <<<'SQL'
            SELECT
                group_id,
                GROUP_CONCAT(id) as book_ids
            FROM books
            WHERE group_id IS NOT NULL
            GROUP BY group_id
SQL
        ), function ($result, $row) {
            $result[$row['group_id']] = explode(',', $row['book_ids']);
            return $result;
        });

        $booksGroups = array_reduce($sql->getArray(
            <<<'SQL'
            SELECT
                id,
                group_id
            FROM books
            WHERE group_id IS NOT NULL
SQL
        ), function ($result, $row) {
            $result[$row['id']] = $row['group_id'];
            return $result;
        });

        $languagePairs = [];
        $activeLanguages = $serviceContainer->get('language')->getActive();
        foreach ($activeLanguages as $index => $language1) {
            foreach (array_slice($activeLanguages, $index + 1) as $language2) {
                $languagePairs[] = [$language1, $language2];
            }
        }

        foreach ($languagePairs as $languagePair) {
            list($language1, $language2) = $languagePair;
            $booksNotInGroups = $sql->getColumn(
                <<<'SQL'
                SELECT id
                FROM books
                WHERE group_id IS NULL
SQL
            );
            $parallangosNotInGroups = $serviceContainer
                ->get('parallango')
                ->getByIds($sql->getArray(
                    <<<'SQL'
                    SELECT id
                    FROM parallangos
                    WHERE
                        left_book_id IN :book_ids_0
                        OR right_book_id IN :book_ids_1
SQL
                    ,
                    [
                        // TODO: probably, fix this somehow
                        'book_ids_0' => $booksNotInGroups,
                        'book_ids_1' => $booksNotInGroups,
                    ]
                ));
print_r($parallangosNotInGroups);
        }

        return 0;
    }
}
