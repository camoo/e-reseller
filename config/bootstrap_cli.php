<?php
declare(strict_types=1);

require_once dirname(__DIR__). '/vendor/autoload.php';
require_once dirname(__DIR__) . '/config/paths.php';
require_once CORE_PATH . 'config' . DS . 'bootstrap.php';
if (file_exists(CONFIG. '.env') && is_readable(CONFIG.'.env')) {
    (new \josegonzalez\Dotenv\Loader(CONFIG.'.env'))->parse()->define();
}

