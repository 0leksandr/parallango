<?php

namespace Base\Commands\Seed;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Seed extends Command
{
    public function configure()
    {
        $this
            ->setName('seed')
            ->setDescription('Seed DB from legacy stuff');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $commands = [
            new Languages(),
            new Authors(),
            new Sections(),
            new Books(),
            new Parallangos(),
            new Groups(),
            new EntityTypes(),
        ];
        $progress = new ProgressBar($output);
        $progress->start(count($commands));
        foreach ($commands as $command) {
            /** @var Command $command */
            $command->run(new ArrayInput([]), $output);
            $progress->advance();
        }
        $progress->finish();
        $output->writeln('');
        return 0;
    }
}
