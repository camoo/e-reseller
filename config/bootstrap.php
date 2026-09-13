<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/vendor/autoload.php';
require_once dirname(__DIR__) . '/config/paths.php';

use Camoo\Cache\Cache;
use CAMOO\Exception\Exception as AppException;
use Camoo\Hosting\Modules;
use CAMOO\Utils\Configure;
use Cake\I18n\I18n;
use josegonzalez\Dotenv\Loader;

if (is_file(CONFIG . '.env') && is_readable(CONFIG . '.env')) {
    new Loader(CONFIG . '.env')->parse()->define();
}

require_once CORE_PATH . 'config' . DS . 'bootstrap.php';

// Local development must remain usable when the remote API is unavailable.
// Remote configuration is still the default outside development and can be
// explicitly enabled locally with USE_REMOTE_CONFIG=true.
$useRemoteConfig = filter_var(getenv('USE_REMOTE_CONFIG') ?: 'false', FILTER_VALIDATE_BOOL);
$isLocalDevelopment = getenv('APP_ENV') === 'development' && !$useRemoteConfig;

if ($isLocalDevelopment) {
    $xConfigHosting = [
        'title_for_layout' => 'Camoo Framework',
        'site_desc_long' => 'Local development environment',
        'tags' => 'camoo,framework,development',
        'domain' => 'framework.local',
        'contact_email' => 'dev@framework.local',
    ];
} elseif (($xConfigHosting = Cache::reads('hosting_conf', '_camoo_hosting_conf')) === false) {
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

    // The hosting dashboard may provide a locale for this reseller. Only
    // activate catalogs that are installed in this application.
    $requestedLocale = $xConfigHosting['locale'] ?? $xConfigHosting['language'] ?? null;
    if (is_string($requestedLocale) && preg_match('/^[a-z]{2}_[A-Z]{2}$/', $requestedLocale)) {
        $localeCatalog = APP . 'Locale' . DS . $requestedLocale . DS . 'default.po';
        if (is_file($localeCatalog)) {
            I18n::setLocale($requestedLocale);
            Configure::write('App.defaultLanguage', $requestedLocale);
        }
    }
}

// TARIFFS

if ($isLocalDevelopment) {
    $xTariffsHosting = ['tariffs' => []];
} elseif (($xTariffsHosting = Cache::reads('hosting_tariffs', '_camoo_hosting_tariff')) === false) {
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
