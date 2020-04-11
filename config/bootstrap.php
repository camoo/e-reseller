<?php
require_once dirname(__DIR__). '/vendor/autoload.php';
require_once dirname(__DIR__) . '/config/paths.php';
require_once CORE_PATH . 'config' . DS . 'bootstrap.php';
if (file_exists(CONFIG. '.env') && is_readable(CONFIG.'.env')) {
    (new \josegonzalez\Dotenv\Loader(CONFIG.'.env'))->parse()->define();
}
use Camoo\Hosting\Modules;
use CAMOO\Cache\Cache;
use CAMOO\Utils\Configure;
use CAMOO\Exception\Exception as AppException;

if (($xConfigHosting = Cache::read('hosting_conf', '_camoo_hosting_conf')) === false) {
    $xConfig = new Modules\Configurations();
    $xConfigResponse = $xConfig->get();
    if ($xConfigResponse->getStatusCode() !== 200) {
        throw new AppException('Site configuration cannot be read!');
    }
    $xConfigHostingRaw = $xConfigResponse->getJson();
    if (!array_key_exists('result', $xConfigHostingRaw)) {
        throw new AppException('Site configuration Result cannot be read!');
    }
    $xConfigHosting =  $xConfigHostingRaw['result'];
    Cache::write('hosting_conf', $xConfigHosting, '_camoo_hosting_conf');
}

if (!empty($xConfigHosting)) {
    Configure::write('RESELLER_SITE', $xConfigHosting);
}


// TARIFFS

if (($xTariffsHosting = Cache::read('hosting_tariffs', '_camoo_hosting_tariff')) === false) {
    $xTariffs = new Modules\Tariffs();
    $xTariffsResponse = $xTariffs->get();
    if ($xTariffsResponse->getStatusCode() !== 200) {
        throw new AppException('Site configuration cannot be read!');
    }
    $xTariffsHostingRaw = $xTariffsResponse->getJson();
    if (!array_key_exists('result', $xTariffsHostingRaw)) {
        throw new AppException('Site configuration Result cannot be read!');
    }
    $xTariffsHosting =  $xTariffsHostingRaw['result'];
    Cache::write('hosting_tariffs', $xTariffsHosting, '_camoo_hosting_tariff');
}

if (!empty($xTariffsHosting)) {
    Configure::write('RESELLER_TARIFFS', $xTariffsHosting);
}
