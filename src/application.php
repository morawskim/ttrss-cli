<?php

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputOption;

define('TT_RSS_CLI_VERSION', '0.1.0');

/** @var \Composer\Autoload\ClassLoader $autoLoader */
$autoLoader = require_once __DIR__ . '/../vendor/autoload.php';

$application = new Application('tt-rrs-cli', TT_RSS_CLI_VERSION);
$application->getDefinition()->addOption(
    new InputOption('tt-rss', 'p', InputOption::VALUE_OPTIONAL,
        'The path to tt-rss docroot', ''));

$command = new \ttrssCli\Commands\ChangeUserPassword();
$application->setCatchExceptions(true);
$application->add($command);
$application->run();
