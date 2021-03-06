#!/usr/bin/php -q 
<?php

require_once __DIR__ . '/../../app/autoload.php';

$application = new \Symfony\Component\Console\Application('base', '1.0.0');
$application->setAutoExit(false);
$application->setCatchExceptions(false);
$application->addCommands([
    new \Base\Commands\DropLegacyTables(),
    new \Base\Commands\Seed\Languages(),
    new \Base\Commands\Seed\Authors(),
    new \Base\Commands\Seed\Sections(),
    new \Base\Commands\Seed\Books(),
    new \Base\Commands\Seed\Parallangos(),
    new \Base\Commands\Seed\Groups(),
    new \Base\Commands\Seed\EntityTypes(),
    new \Base\Commands\Seed\Seed(),
    new \Base\Commands\ParseParagraphs(),
    new \Base\Commands\MaterializePages(),
    new \Base\Commands\MaterializeNrBooks(),
    new \Base\Commands\UpdateDB(),
    new \Base\Commands\CheckDB(),
]);
exit($application->run());
