<?php

declare(strict_types=1);

use josegonzalez\Dotenv\Loader;

require_once dirname(__DIR__) . '/vendor/autoload.php';
require_once dirname(__DIR__) . '/config/paths.php';
require_once CORE_PATH . 'config' . DS . 'bootstrap.php';
$dotenvCandidates = [
    CONFIG . '.env',
    CONFIG . 'config.env',
    ROOT . DS . '.env',
    ROOT . DS . 'config.env',
];
foreach ($dotenvCandidates as $dotenvFile) {
    if (is_file($dotenvFile) && is_readable($dotenvFile)) {
        (new Loader($dotenvFile))->parse()->skipExisting()->putenv()->toEnv()->toServer()->define();
    }
}

