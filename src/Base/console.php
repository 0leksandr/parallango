#!/usr/bin/php -q 
<?php

require_once __DIR__ . '/../../app/autoload.php';

$application = new \Symfony\Component\Console\Application('base', '1.0.0');
$application->setAutoExit(false);
$application->setCatchExceptions(false);
$application->addCommands([
    new \Base\ParseParagraphs(),
]);
exit($application->run());
