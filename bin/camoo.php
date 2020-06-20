#!/usr/bin/php -q
<?php
declare(strict_types=1);

if (version_compare(PHP_VERSION, '7.2.0', '<')) {
    trigger_error('The CAMOO FRAMEWORK Library requires PHP version 7.2.0 or higher', E_USER_ERROR);
    exit;
}


try {
    if (!file_exists(dirname(__DIR__) . '/config/bootstrap_cli.php')) {
        throw new Exception('Bootstrap file not found');
    }
    require_once dirname(__DIR__) . '/config/bootstrap_cli.php';
} catch (Exception $err) {
    trigger_error($err->getMessage(), E_USER_ERROR);
    exit;
}

use CAMOO\Console\Runner;

$oRunner = new Runner();
exit($oRunner->run($argv));
