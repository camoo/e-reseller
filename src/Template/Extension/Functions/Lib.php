<?php

declare(strict_types=1);

namespace App\Template\Extension\Functions;

use Camoo\Cache\Cache;
use CAMOO\Template\Extension\FunctionHelper;

/**
 * Class Lib
 *
 * @author CamooSarl
 */
final class Lib extends FunctionHelper
{
    public $functions = ['Basket'];

    public function getFunctions(): array
    {
        return [
            $this->add('domainwhois_results', [$this, 'getDomainWhoisResult'], ['is_safe' => ['html']]),
            $this->add('add_custom_css', [$this, 'addCustomCss']),
            $this->add('add_custom_js', [$this, 'addCustomJs']),
            $this->add('get_logo_name', [$this, 'getLogoName'], ['is_safe' => ['html']]),
            $this->add('get_favicon_name', [$this, 'getFaviconName'], ['is_safe' => ['html']]),
        ];
    }

    public function getDomainWhoisResult($inp)
    {
        $oBasket = $this->Basket->getItems();
        $result = '';
        if (($xRet = Cache::reads($inp, '_camoo_hosting_1hour')) !== false) {
            foreach ($xRet as $domain => $value) {
                $class = 'available domain-available add-to-basket';
                $word = 'Disponible';
                $takeIt = 'Je commande';
                $cmd = 'add-to-basket';
                if ($oBasket->has($domain)) {
                    $cmd .= ' disable';
                    $takeIt = $word = 'Dans le pannier';
                }

                if ($value['status'] === 'N') {
                    $class = 'disable domain-taken';
                    $word = 'déjà pris';
                    $cmd = 'disable';
                }
                $result .= sprintf('
                    <div class="single_search d-flex justify-content-between align-items-center">
                        <div class="name_title">
                            <h4>' . $domain . '</h4>
                        </div>
                        <div class="prising_content single-domain-item">
                            <a data-domain="' . $domain . '" data-price="' . $value['price']['addnewdomain'] . '" class="trigger-domain premium %s" href="#">%s</a>
                            <a href="#">XAF ' . $value['price']['addnewdomain'] . '/an</a> 
                            <a data-domain="' . $domain . '" data-price="' . $value['price']['addnewdomain'] . '" class="trigger-domain boxed_btn_green %s" href="#">%s</a>
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
        if (!is_file($cssPath . $filename)) {
            return false;
        }

        return true;
    }

    public function addCustomJs(): bool
    {
        $jsPath = WEB . 'js' . DS;
        $filename = 'custom.js';
        if (!is_file($jsPath . $filename)) {
            return false;
        }

        return true;
    }

    public function getLogoName(): string
    {
        if (!defined('LOGO_FILE_NAME')) {
            return 'logo.png';
        }
        $imgPath = WEB . 'img' . DS;
        $filename = LOGO_FILE_NAME;
        if (!is_file($imgPath . $filename)) {
            return 'logo.png';
        }

        return LOGO_FILE_NAME;
    }

    public function getFaviconName(): string
    {
        if (!defined('FAVICON_FILE_NAME')) {
            return 'favicon.ico';
        }
        $imgPath = WEB;
        $filename = FAVICON_FILE_NAME;
        if (!is_file($imgPath . $filename)) {
            return 'favicon.ico';
        }

        return FAVICON_FILE_NAME;
    }
}
