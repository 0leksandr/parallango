<?php

namespace Base\Commands\Seed;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Utils\DB\Literal;
use Utils\DB\SQL;
use Utils\ServiceContainer;

require_once __DIR__ . '/../../../Utils/Utils.php';

abstract class AbstractSeedCommand extends Command
{
    /** @var SQL */
    private $sql;

    /**
     * @return string[]
     */
    abstract protected function getTableNames();

    abstract protected function seed();

    public function __construct()
    {
        parent::__construct();
        $this->sql = ServiceContainer::get('prod')->get('sql');
    }

    public function configure()
    {
        $table = head($this->getTableNames());
        $this
            ->setName('seed:' . $table)
            ->setDescription(sprintf(
                'Copy data to %s from legacy stuff',
                $table
            ));
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        foreach (array_reverse($this->getTableNames()) as $tableName) {
            $this->sql->execute(
                <<<'SQL'
                TRUNCATE TABLE :table_name;
SQL
                ,
                ['table_name' => new Literal($tableName)]
            );
        }
        $this->seed();
        return 0;
    }

    /**
     * @return SQL
     */
    protected function sql()
    {
        return $this->sql;
    }
}
