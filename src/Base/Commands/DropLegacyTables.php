<?php

namespace Base\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Utils\DB\Literal;
use Utils\ServiceContainer;

class DropLegacyTables extends Command
{
    public function configure()
    {
        $this
            ->setName('drop:legacy')
            ->setDescription('Drop legacy stuff');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $sql = ServiceContainer::get('prod')->get('sql');
        foreach ([
            'aldebaran.ru_authors',
            'aldebaran.ru_books',
            'aldebaran.ru_sections',

            'allbooks.com.ua_authors',
            'allbooks.com.ua_books',
            'allbooks.com.ua_complete_pages',
            'allbooks.com.ua_sections',
            'allbooks.com.ua_working_pages',

            'royallib.ru_authors',
            'royallib.ru_books',
            'royallib.ru_complete_pages',
            'royallib.ru_prematched_titles',
            'royallib.ru_prematched_titles_0',
            'royallib.ru_sections',
            'royallib.ru_selected_titles',
            'royallib.ru_working_pages',

            'tululu.org_authors',
            'tululu.org_books',
            'tululu.org_complete_pages',
            'tululu.org_sections',
            'tululu.org_working_pages',
        ] as $table) {
            $sql->execute(
                <<<'SQL'
                DROP TABLE IF EXISTS `:table`
SQL
                ,
                ['table' => new Literal($table)]
            );
        }

        return 0;
    }
}
