<?php

namespace Base;

use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Utils\DB\ValuesList;
use Utils\ServiceContainer;

require_once __DIR__ . '/../../../../../scripts/config.php';
require_once __DIR__ . '/../Utils/Utils.php';

class ParseParagraphs extends Command
{
    const OPTION_ALL = 'all';
    const OPTION_NEW = 'new';

    public function configure()
    {
        $this
            ->setName('parse:paragraphs')
            ->setDescription('Write paragraphs (row) lengths into DB')
            ->addOption(
                self::OPTION_ALL,
                null,
                InputOption::VALUE_NONE
            )
            ->addOption(
                self::OPTION_NEW,
                null,
                InputOption::VALUE_NONE
            );
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws Exception
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $all = $input->getOption(self::OPTION_ALL);
        $new = $input->getOption(self::OPTION_NEW);
        if ($all && $new) {
            throw new Exception('Only one mode should be specified');
        }
        if (!$all && !$new) {
            $new = true;
        }

        $serviceContainer = ServiceContainer::get('prod');
        $sql = $serviceContainer->get('sql');
        $path = $serviceContainer->getParameter('books_root');

        if ($all) {
            $sql->execute(
                <<<'SQL'
                TRUNCATE paragraphs;
SQL
            );
        }
        $existingParallangoIds = $sql->getColumn(
            <<<'SQL'
            SELECT DISTINCT parallango_id
            FROM paragraphs;
SQL
        );

        $filenames = _scandir($path);

        $progress = new ProgressBar($output, count($filenames));
        $progress->start();

        foreach ($filenames as $filename) {
            $parallangoId =
                intval(_preg_match('#^(\d+)\.html$#', $filename)[1]);
            if ($new && in_array($parallangoId, $existingParallangoIds)) {
                $progress->advance();
                continue;
            }
            $file = file_get_contents("$path/$filename");
            $rows = get_text_between_all($file, '<tr>', '</tr>', false, true);
            $caret = strlen('<table>');

            $sql->execute(
                <<<'SQL'
                INSERT INTO paragraphs (
                    parallango_id,
                    `order`,
                    position_begin,
                    position_end
                )
                VALUES :values;
SQL
                ,
                [
                    'values' => new ValuesList(
                        array_map(
                            function (
                                $index,
                                $row
                            ) use (
                                $parallangoId,
                                &$caret
                            ) {
                                $caretPrev = $caret;
                                $caret += strlen($row);
                                return [
                                    $parallangoId,
                                    $index,
                                    $caretPrev,
                                    $caret - 1,
                                ];
                            },
                            array_keys($rows),
                            $rows
                        )
                    ),
                ]
            );

            $progress->advance();
        }

        $progress->finish();
        $output->writeln('');
        return 0;
    }
}
