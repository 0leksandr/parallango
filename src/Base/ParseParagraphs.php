<?php

namespace Base;

use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Utils\DB\SQL;
use Utils\DB\ValuesList;
use Utils\ServiceContainer;

require_once __DIR__ . '/../../../../../scripts/config.php';
require_once __DIR__ . '/../Utils/Utils.php';

class ParseParagraphs extends Command
{
    const OPTION_ALL = 'all';
    const OPTION_NEW = 'new';

    /** @var ContainerInterface */
    private $serviceContainer;
    /** @var SQL */
    private $sql;

    public function __construct()
    {
        parent::__construct();
        $this->serviceContainer = ServiceContainer::get('prod');
        $this->sql = $this->serviceContainer->get('sql');
    }

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
        $all = (bool)$input->getOption(self::OPTION_ALL);
        $new = (bool)$input->getOption(self::OPTION_NEW);
        if ($all && $new) {
            throw new Exception('Only one mode should be specified');
        }
        if (!$all && !$new) {
            throw new Exception(sprintf(
                'No mode specified. Please select --%s or --%s',
                self::OPTION_ALL,
                self::OPTION_NEW
            ));
        }

        $path = $this->serviceContainer->getParameter('books_root');

        if ($all) {
            $this->truncateParagraphs();
        }
        $parallangoIds = $this->getParallangoIds();

        $filenames = _scandir($path);

        $progress = new ProgressBar($output, count($filenames));
        $progress->start();

        foreach ($filenames as $filename) {
            $parallangoId = intval(
                _preg_match('#^(\d+)\.html$#', $filename)[1]
            );
            if ($new && in_array($parallangoId, $parallangoIds)) {
                $progress->advance();
                continue;
            }
            $file = file_get_contents("$path/$filename");
            $this->process($parallangoId, $file);

            $progress->advance();
        }

        $progress->finish();
        $output->writeln('');
        return 0;
    }

    private function truncateParagraphs()
    {
        $this->sql->execute(
            <<<'SQL'
            TRUNCATE paragraphs;
SQL
        );
    }

    /**
     * @return int[]
     */
    private function getParallangoIds()
    {
        return $this->sql->getColumn(
            <<<'SQL'
            SELECT DISTINCT parallango_id
            FROM paragraphs;
SQL
        );
    }

    /**
     * @param int $parallangoId
     * @param string $file
     */
    private function process($parallangoId, $file)
    {
        $rows = get_text_between_all($file, '<tr>', '</tr>', false, true);

        $this->sql->execute(
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
                    $this->getParagraphsData($parallangoId, $rows)
                ),
            ]
        );
    }

    /**
     * @param int $parallangoId
     * @param string[] $rows
     * @return array
     */
    private function getParagraphsData($parallangoId, array $rows)
    {
        $caret = strlen('<table>');
        return array_map(
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
                    'id' => $parallangoId,
                    'order' => $index,
                    'position_begin' => $caretPrev,
                    'position_end' => $caret - 1,
                ];
            },
            array_keys($rows),
            $rows
        );
    }
}
