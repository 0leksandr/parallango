<?php

namespace Base\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Utils\DB\Literal;
use Utils\DB\SQL;
use Utils\ServiceContainer;

class CheckDB extends Command
{
    /** @var SQL */
    private $sql;

    public function __construct()
    {
        parent::__construct();
        $this->sql = ServiceContainer::get('prod')->get('sql');
    }

    public function configure()
    {
        $this
            ->setName('check:db')
            ->setDescription(
                'Check DB tables for being filled (migrations should be upped)'
            );
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $table = new Table($output);
        $table->setHeaders(['Table', 'Is not empty', 'Command to fill']);
        foreach ([
//            'languages',
//            'authors',
//            'sections',
//            'books',
//            'parallangos',
            'paragraphs' => ParseParagraphs::class,
            'materialized_pages' => MaterializePages::class,
            'mat_nr_books_authors' => MaterializeNrBooks::class,
            'mat_nr_books_sections' => MaterializeNrBooks::class,
        ] as $tableName => $command) {
            $filled = $this->sql->getSingle(
                <<<'SQL'
                SELECT COUNT(*)
                FROM :table_name
SQL
                ,
                ['table_name' => new Literal($tableName)]
            ) !== 0 ? '+' : '-';
            $table->addRow([$tableName, $filled, $command]);
        }
        $table->render();
        return 0;
    }
}
