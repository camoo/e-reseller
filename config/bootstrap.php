<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/vendor/autoload.php';
require_once dirname(__DIR__) . '/config/paths.php';

use Camoo\Cache\Cache;
use App\Service\DomainPricingService;
use CAMOO\Exception\Exception as AppException;
use Camoo\Hosting\Modules;
use CAMOO\Utils\Configure;
use Cake\I18n\I18n;
use josegonzalez\Dotenv\Loader;

if (is_file(CONFIG . '.env') && is_readable(CONFIG . '.env')) {
    new Loader(CONFIG . '.env')->parse()->skipExisting()->putenv()->toEnv()->toServer()->define();
}

require_once CORE_PATH . 'config' . DS . 'bootstrap.php';

// CakePHP's translation helpers are function-based and are not guaranteed to
// be loaded by Composer when only the I18n classes are referenced.
require_once ROOT . DS . 'vendor' . DS . 'cakephp' . DS . 'i18n' . DS . 'functions.php';

// Apply the configured error mask before framework code initializes services
// that may emit deprecation notices (for example, CakePHP translations).
error_reporting((int)(Configure::read('Error.errorLevel') ?? E_ALL));

// Optional PHP configuration is the right place for long-form reseller
// content. Keep app.local.php untracked.
$localConfigPath = CONFIG . 'app.local.php';
if (is_file($localConfigPath) && is_readable($localConfigPath)) {
    Configure::load($localConfigPath, true);
}

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

    // Homepage content is intentionally data-driven so a reseller can edit
    // merchandising copy and feature cards without modifying Twig templates.
    $homeContent = Configure::read('HomeContent');
    if (!is_array($homeContent)) {
        // Older production deployments may not have the HomeContent defaults
        // yet, but pricing and remote overrides must still be safe to apply.
        $homeContent = [];
    }
    $mergeHomeContent = static function (array $defaults, array $override) use (&$mergeHomeContent): array {
        foreach ($override as $key => $value) {
            // Lists represent editable collections: supplying one replaces
            // the default list instead of unexpectedly merging by numeric key.
            if (
                is_array($value)
                && is_array($defaults[$key] ?? null)
                && !array_is_list($value)
                && !array_is_list($defaults[$key])
            ) {
                $defaults[$key] = $mergeHomeContent($defaults[$key], $value);
                continue;
            }

            $defaults[$key] = $value;
        }

        return $defaults;
    };
    $remoteHomeContent = $xConfigHosting['home_content']
        ?? $xConfigHosting['homepage']
        ?? [];
    if (is_string($remoteHomeContent) && trim($remoteHomeContent) !== '') {
        $remoteHomeContent = json_decode($remoteHomeContent, true) ?: [];
    }
    if (is_array($remoteHomeContent) && is_array($homeContent)) {
        $homeContent = $mergeHomeContent($homeContent, $remoteHomeContent);
    }
    $homeContentJson = getenv('HOME_CONTENT_JSON');
    if (is_string($homeContentJson) && trim($homeContentJson) !== '') {
        $localHomeContent = json_decode($homeContentJson, true);
        if (is_array($localHomeContent) && is_array($homeContent)) {
            $homeContent = $mergeHomeContent($homeContent, $localHomeContent);
        }
    }

    // Domain prices are authoritative data from the hosting API, not static
    // homepage copy. Cache the SDK lookup so rendering the homepage does not
    // add a provider request on every visit.
    $domainPricingConfig = Configure::read('DomainPricing');
    if (!is_array($domainPricingConfig)) {
        $domainPricingConfig = [
            'enabled' => filter_var(getenv('DOMAIN_PRICES_FROM_SDK') ?: 'true', FILTER_VALIDATE_BOOL),
            'tlds' => ['cm', 'com', 'net', 'org'],
        ];
    }
    if (is_array($domainPricingConfig) && ($domainPricingConfig['enabled'] ?? true) === true) {
        $domainPrices = Cache::reads('__homepage_domain_prices__', '_camoo_hosting_1hour');
        $domainTlds = is_array($domainPricingConfig['tlds'] ?? null)
            ? $domainPricingConfig['tlds'] : [];

        if (!is_array($domainPrices)) {
            try {
                $domainPrices = (new DomainPricingService())->fetch(
                    $domainTlds,
                );
            } catch (\Throwable) {
                $domainPrices = [];
            }

            // This fallback is deliberately limited to the explicit local
            // development fixture. Production never invents a domain price.
            $localAvailability = Configure::read('DomainAvailability.local');
            if ($domainPrices === []
                && is_array($localAvailability)
                && ($localAvailability['enabled'] ?? false) === true
            ) {
                $domainPrices = DomainPricingService::localFixturePrices($localAvailability, $domainTlds);
            }

            if ($domainPrices !== []) {
                Cache::writes('__homepage_domain_prices__', $domainPrices, '_camoo_hosting_1hour');
            }
        }

        $homeContent = DomainPricingService::applyToHomeContent(
            $homeContent,
            is_array($domainPrices) ? $domainPrices : [],
            $domainTlds,
        );
    }
    Configure::write('HomeContent', $homeContent);
    // Keep the descriptive alias available to templates and extensions while
    // preserving the existing HomeContent configuration key.
    Configure::write('ShowcaseContent', $homeContent);

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
