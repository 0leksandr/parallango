<?php

namespace Base\Commands;

use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Utils\DB\Result;
use Utils\DB\SQL;
use Utils\DB\ValuesList;
use Utils\ServiceContainer;

require_once __DIR__ . '/../../Utils/Utils.php';

class MaterializePages extends Command
{
    const ARGUMENT_PAGE_SIZE = 'page-size';
    const OPTION_RESET = 'reset';

    /** @var SQL */
    private $sql;

    public function configure()
    {
        $this
            ->setName('materialize:pages')
            ->setDescription('Save paragraphs, matched for pages sizes')
            ->addArgument(
                self::ARGUMENT_PAGE_SIZE,
                InputArgument::IS_ARRAY | InputArgument::REQUIRED,
                'page sizes, separated by space'
            )
            ->addOption(
                self::OPTION_RESET,
                'r',
                InputOption::VALUE_NONE,
                'delete previously materialized pages'
            );
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $pageSizes = $input->getArgument(self::ARGUMENT_PAGE_SIZE);
        $this->sql = ServiceContainer::get('prod')->get('sql');

        if ($input->getOption(self::OPTION_RESET)) {
            $this->sql->execute(
                <<<'SQL'
                TRUNCATE materialized_pages;
SQL
            );
        }

        $milestones = $this->getAllMilestones(
            $pageSizes,
            new ProgressBar($output)
        );
        $output->writeln('');
        $this->insertMilestones($milestones, new ProgressBar($output));
        $output->writeln('');

        return 0;
    }

    /**
     * @param int[] $pageSizes
     * @param ProgressBar $progress
     * @return array[]
     * @throws Exception
     */
    private function getAllMilestones(array $pageSizes, ProgressBar $progress)
    {
        $nrParallangos = $this->sql->getSingle(
            <<<'SQL'
            SELECT COUNT(*)
            FROM parallangos
SQL
        );
        if ($this->sql->getSingle(
            <<<'SQL'
            SELECT COUNT(DISTINCT parallango_id)
            FROM paragraphs
SQL
        ) !== $nrParallangos) {
            throw new Exception('Numbers of paragraphs do not match');
        }

        $progress->start(count($pageSizes) * $nrParallangos);
        $milestones = [];
        $paragraphsStmt = $this->sql->prepare(
            <<<'SQL'
            SELECT
                id,
                parallango_id,
                position_end
            FROM
                paragraphs
            ORDER BY
                parallango_id,
                id
SQL
        );
        foreach ($pageSizes as $pageSize) {
            $pageSizeId = $this->getPageSizeId($pageSize);
            $this->deletePreviouslyCreatedPages($pageSizeId);
            $paragraphsRes = $paragraphsStmt
                ->execute()
                ->getResultBatchIndexed('parallango_id');
            while ($paragraphs = $paragraphsRes->fetchBatchArray()) {
                $parallangoId = head($paragraphs)['parallango_id'];
                $milestones = array_merge($milestones, array_map(
                    function ($milestone) use ($parallangoId, $pageSizeId) {
                        return [
                            $parallangoId,
                            $pageSizeId,
                            $milestone,
                        ];
                    },
                    $this->getMilestones($paragraphs, $pageSize)
                ));
                $progress->advance();
            }
        }
        $progress->finish();

        return $milestones;
    }

    /**
     * @param int $pageSize
     * @return int
     */
    private function getPageSizeId($pageSize)
    {
        $pageSizeId = $this->sql->getSingle(
            <<<'SQL'
            SELECT id
            FROM page_sizes
            WHERE page_size_symbols = :page_size
SQL
            ,
            ['page_size' => $pageSize]
        );
        if ($pageSizeId === Result::NO_SINGLE_VALUE) {
            $this->sql->execute(
                <<<'SQL'
                INSERT INTO page_sizes (page_size_symbols)
                VALUE (:page_size);
SQL
                ,
                ['page_size' => $pageSize]
            );
            $pageSizeId = $this->sql->lastInsertId();
        }
        return $pageSizeId;
    }

    /**
     * @param int $pageSizeId
     */
    private function deletePreviouslyCreatedPages($pageSizeId)
    {
        $this->sql->execute(
            <<<'SQL'
            DELETE FROM materialized_pages
            WHERE page_size_id = :page_size_id
SQL
            ,
            ['page_size_id' => $pageSizeId]
        );
    }

    /**
     * @param array $paragraphs
     * @param int $pageSize
     * @return int[]
     */
    private function getMilestones(array $paragraphs, $pageSize)
    {
        $milestones = [1];
        $prevEnd = strlen('<table>');
        foreach (
            array_slice($paragraphs, 0, -1) as $index => $paragraph
        ) {
            $nextParagraph = $paragraphs[$index + 1];
            $thisEnd = $paragraph['position_end'];
            $nextEnd = $nextParagraph['position_end'];
            if ($thisEnd - $prevEnd >= $pageSize) {
                $milestones[] = $paragraph['id'];
                $prevEnd = $thisEnd;
                continue;
            }
            if ($nextEnd - $prevEnd <= $pageSize) {
                continue;
            }
            $milestone = $prevEnd + $pageSize;
            if ($milestone - $thisEnd < $nextEnd - $milestone) {
                $milestones[] = $paragraph['id'];
                $prevEnd = $thisEnd;
            } else {
                $milestones[] = $nextParagraph['id'];
                $prevEnd = $nextEnd;
            }
        }
        return $milestones;
    }

    /**
     * @param array $milestones
     * @param ProgressBar $progress
     */
    private function insertMilestones(
        array $milestones,
        ProgressBar $progress
    ) {
        $chunks = array_chunk($milestones, 1000);
        $progress->start(count($chunks));
        foreach ($chunks as $chunk) {
            $this->sql->prepare(
                <<<'SQL'
                INSERT INTO materialized_pages (
                    parallango_id,
                    page_size_id,
                    paragraph_id
                )
                VALUES :values;
SQL
            )->execute(['values' => new ValuesList($chunk)]);
            $progress->advance();
        }
        $progress->finish();
    }
}
