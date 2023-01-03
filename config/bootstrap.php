<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/vendor/autoload.php';
require_once dirname(__DIR__) . '/config/paths.php';
require_once CORE_PATH . 'config' . DS . 'bootstrap.php';

use Camoo\Cache\Cache;
use CAMOO\Exception\Exception as AppException;
use Camoo\Hosting\Modules;
use CAMOO\Utils\Configure;
use josegonzalez\Dotenv\Loader;

if (file_exists(CONFIG . '.env') && is_readable(CONFIG . '.env')) {
    (new Loader(CONFIG . '.env'))->parse()->define();
}

if (($xConfigHosting = Cache::reads('hosting_conf', '_camoo_hosting_conf')) === false) {
    $xConfig = new Modules\Configurations();
    $xConfigResponse = $xConfig->get();
    if ($xConfigResponse->getStatusCode() !== 200) {
        throw new AppException('Site configuration cannot be read!');
    }
    $xConfigHostingRaw = $xConfigResponse->getJson();
    if (!array_key_exists('result', $xConfigHostingRaw)) {
        throw new AppException('Site configuration Result cannot be read!');
    }
    $xConfigHosting = $xConfigHostingRaw['result'];
    Cache::writes('hosting_conf', $xConfigHosting, '_camoo_hosting_conf');
}

if (!empty($xConfigHosting)) {
    Configure::write('RESELLER_SITE', $xConfigHosting);
}

// TARIFFS

if (($xTariffsHosting = Cache::reads('hosting_tariffs', '_camoo_hosting_tariff')) === false) {
    $xTariffs = new Modules\Tariffs();
    $xTariffsResponse = $xTariffs->get();
    if ($xTariffsResponse->getStatusCode() !== 200) {
        throw new AppException('Site configuration cannot be read!');
    }
    $xTariffsHostingRaw = $xTariffsResponse->getJson();
    if (!array_key_exists('result', $xTariffsHostingRaw)) {
        throw new AppException('Site configuration Result cannot be read!');
    }
    $xTariffsHosting = $xTariffsHostingRaw['result'];
    Cache::writes('hosting_tariffs', $xTariffsHosting, '_camoo_hosting_tariff');
}

if (!empty($xTariffsHosting)) {
    Configure::write('RESELLER_TARIFFS', $xTariffsHosting);
}
