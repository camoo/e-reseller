<?php

declare(strict_types=1);

namespace App\Template\Extension\Functions;

use Camoo\Cache\Cache;
use CAMOO\Template\Extension\FunctionHelper;
use CAMOO\Utils\Cart as BasketRepository;
use CAMOO\Utils\Configure;
use function Cake\I18n\__;

/**
 * Class Lib
 *
 * @author CamooSarl
 */
final class Lib extends FunctionHelper
{
    public array $functions = ['Basket'];

    public function getFunctions(): array
    {
        return [
            $this->add('domainwhois_results', $this->getDomainWhoisResult(...), ['is_safe' => ['html']]),
            $this->add('add_custom_css', $this->addCustomCss(...)),
            $this->add('add_custom_js', $this->addCustomJs(...)),
            $this->add('get_logo_name', $this->getLogoName(...), ['is_safe' => ['html']]),
            $this->add('get_favicon_name', $this->getFaviconName(...), ['is_safe' => ['html']]),
            $this->add('asset_version', $this->assetVersion(...)),
            $this->add('t', $this->translate(...)),
            $this->add('feature_enabled', $this->featureEnabled(...)),
        ];
    }

    public function getDomainWhoisResult($inp): string
    {
        $oBasket = BasketRepository::create($this->request);
        if ($oBasket === null) {
            return '';
        }

        $result = '';
        if (($xRet = Cache::reads($inp, '_camoo_hosting_1hour')) !== false && is_array($xRet)) {
            foreach ($xRet as $domain => $value) {
                if (!is_array($value) || !array_key_exists('status', $value)) {
                    continue;
                }

                $class = 'available domain-available add-to-basket';
                $word = 'Disponible';
                $takeIt = 'Je commande';
                $cmd = 'add-to-basket';
                if ($oBasket->has($domain)) {
                    $cmd .= ' disable';
                    $takeIt = 'Dans le pannier';
                    $word = 'Dans le pannier';
                }

                if ($value['status'] === 'N') {
                    $class = 'disable domain-taken';
                    $word = 'déjà pris';
                    $cmd = 'disable';
                }

                $escapedDomain = $this->escape((string)$domain);
                $price = $this->escape((string)($value['price']['addnewdomain'] ?? ''));
                $result .= sprintf('
                    <div class="single_search d-flex justify-content-between align-items-center">
                        <div class="name_title">
                            <h4>' . $escapedDomain . '</h4>
                        </div>
                        <div class="prising_content single-domain-item">
                            <a data-domain="' . $escapedDomain . '" data-price="' . $price . '" class="trigger-domain premium %s" href="#">%s</a>
                            <a href="#">XAF ' . $price . '/an</a>
                            <a data-domain="' . $escapedDomain . '" data-price="' . $price . '" class="trigger-domain boxed_btn_green %s" href="#">%s</a>
                        </div>
                    </div>', $class, $word, $cmd, $takeIt);
            }
        }

        return $result;
    }

    public function addCustomCss(): bool
    {
        $cssPath = WEB . 'css' . DS;
        $filename = 'custom.css';
        return is_file($cssPath . $filename);
    }

    public function addCustomJs(): bool
    {
        $jsPath = WEB . 'js' . DS;
        $filename = 'custom.js';
        return is_file($jsPath . $filename);
    }

    public function getLogoName(): string
    {
        if (!defined('LOGO_FILE_NAME')) {
            return 'logo.png';
        }

        $imgPath = WEB . 'img' . DS;
        $filename = basename((string)LOGO_FILE_NAME);
        if ($filename !== (string)LOGO_FILE_NAME || $filename === '') {
            return 'logo.png';
        }
        if (!is_file($imgPath . $filename)) {
            return 'logo.png';
        }

        return $filename;
    }

    public function getFaviconName(): string
    {
        if (!defined('FAVICON_FILE_NAME')) {
            return 'favicon.ico';
        }

        $imgPath = WEB;
        $filename = basename((string)FAVICON_FILE_NAME);
        if ($filename !== (string)FAVICON_FILE_NAME || $filename === '') {
            return 'favicon.ico';
        }
        if (!is_file($imgPath . $filename)) {
            return 'favicon.ico';
        }

        return $filename;
    }

    public function assetVersion(string $asset): string
    {
        $asset = ltrim($asset, '/');
        if ($asset === '' || str_contains($asset, '..')) {
            return '';
        }

        $path = WEB . $asset;
        $mtime = is_file($path) ? filemtime($path) : false;

        return false === $mtime ? '' : '?v=' . $mtime;
    }

    /** Translate text from the active reseller locale in Twig templates. */
    public function translate(string $message, mixed ...$arguments): string
    {
        return __($message, ...$arguments);
    }

    public function featureEnabled(string $feature): bool
    {
        $features = Configure::read('Features');

        return is_array($features) && ($features[$feature] ?? false) === true;
    }

    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
