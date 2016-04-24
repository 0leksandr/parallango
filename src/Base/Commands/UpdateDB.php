<?php

namespace Base\Commands;

use Base\Commands\Seed\Seed;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateDB extends Command
{
    const ARGUMENT_PAGE_SIZE = 'page-size';

    public function configure()
    {
        $this
            ->setName('update:db')
            ->setDescription('Get brand new DB')
            ->addArgument(
                self::ARGUMENT_PAGE_SIZE,
                InputArgument::IS_ARRAY | InputArgument::REQUIRED
            );
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $pageSize = $input->getArgument(self::ARGUMENT_PAGE_SIZE);
        foreach ([
            [
                'message' => 'Seeding DB',
                'command' => new Seed(),
                'input' => [],
            ],
            [
                'message' => 'Parsing paragraphs',
                'command' => new ParseParagraphs(),
                'input' => ['--' . ParseParagraphs::OPTION_ALL => true],
            ],
            [
                'message' => 'Materializing pages',
                'command' => new MaterializePages(),
                'input' => [
                    '--' . MaterializePages::OPTION_RESET => true,
                    MaterializePages::ARGUMENT_PAGE_SIZE => $pageSize,
                ],
            ],
            [
                'message' => 'Materializing nr books',
                'command' => new MaterializeNrBooks(),
                'input' => [],
            ],
        ] as $step) {
            $output->writeln($step['message'] . '...');
            /** @var Command $command */
            $command = $step['command'];
            $command->run(new ArrayInput($step['input']), $output);
        }
        return 0;
    }
}
