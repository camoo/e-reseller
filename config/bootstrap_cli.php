<?php

declare(strict_types=1);

use josegonzalez\Dotenv\Loader;

require_once dirname(__DIR__) . '/vendor/autoload.php';
require_once dirname(__DIR__) . '/config/paths.php';
require_once CORE_PATH . 'config' . DS . 'bootstrap.php';
if (is_file(CONFIG . '.env') && is_readable(CONFIG . '.env')) {
    (new Loader(CONFIG . '.env'))->parse()->define();
}
