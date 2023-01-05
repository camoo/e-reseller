#!/usr/bin/php -q
<?php
declare(strict_types=1);

if (version_compare(PHP_VERSION, '8.0', '<')) {
    trigger_error('The CAMOO FRAMEWORK Library requires PHP version 8.0 or higher', E_USER_ERROR);
}

try {
    if (!file_exists(dirname(__DIR__) . '/config/bootstrap_cli.php')) {
        throw new Exception('Bootstrap file not found');
    }
    require_once dirname(__DIR__) . '/config/bootstrap_cli.php';
} catch (Exception $err) {
    trigger_error($err->getMessage(), E_USER_ERROR);
}

use CAMOO\Console\CommandFinder;
use CAMOO\Console\Runner;

if (count($argv) < 2) {
    $finder = new CommandFinder();
    $finder->find();
    exit(1);
}

$runner = new Runner($argv);
$runner->run();
exit(1);
