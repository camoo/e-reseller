<?php

declare(strict_types=1);

namespace App\Template\Extension\Functions;

use CAMOO\Template\Extension\FunctionHelper;
use CAMOO\Utils\Configure;
use CAMOO\Utils\Inflector;

/**
 * Class Tariffs
 *
 * @author CamooSarl
 */
class Tariffs extends FunctionHelper
{
    public function getFunctions(): array
    {
        return [
            $this->add('hosting_plans', [$this, 'getHostingPlans'], ['is_safe' => ['html']]),
            $this->add('package_plans', [$this, 'getPackagePlans'], ['is_safe' => ['html']]),
        ];
    }

    public function getHostingPlans(): string
    {
        $html = '';
        if (($all = Configure::read('RESELLER_TARIFFS')) && array_key_exists('tariffs', $all)) {
            $ahTariffs = $all['tariffs'];
            $count = 0;
            foreach ($ahTariffs as $ahTariff) {
                if (array_key_exists('package_type', $ahTariff) && $ahTariff['package_type']['id'] === 2) {
                    $html .= $this->_planHtml($ahTariff);
                    $count++;
                    if ($count > 3) {
                        break;
                    }
                }
            }
        }

        return $html;
    }

    public function getPackagePlans(): string
    {
        $headerOptions = ['', 'deep', 'yellow'];
        $html = '';
        if (($all = Configure::read('RESELLER_TARIFFS')) && array_key_exists('tariffs', $all)) {
            $ahTariffs = $all['tariffs'];
            $count = 0;
            foreach ($ahTariffs as $ahTariff) {
                if (array_key_exists('package_type', $ahTariff) && $ahTariff['package_type']['id'] === 2) {
                    $html .= $this->generatePackageHtml($ahTariff, $headerOptions[$count]);
                    $count++;
                    if ($count > 2) {
                        break;
                    }
                }
            }
        }

        return $html;
    }

    private function generatePackageHtml(array $hTariff, string $headerClass): string
    {
        $html = '
                <div class="col-xl-4 col-md-6 col-lg-4"">
                    <div class="single_prising">
                        <div class="prising_header '.$headerClass.'">
                            <h3>' . Inflector::humanize($hTariff['name']) . '</h3>
                        </div>
                        
                               <div class="middle_content">
        <div class="list">
        <ul>
                ' . $this->inclDomains($hTariff) . '
                ' . $this->storage($hTariff, 'ram_quota') . '
                ' . $this->storage($hTariff) . '
                ' . $this->storage($hTariff, 'bwlimit') . '
                ' . $this->humanOptions($hTariff, 'max_subdomain') . '
                ' . $this->humanOptions($hTariff, 'max_pop') . '
                ' . $this->humanOptions($hTariff, 'max_mysql') . '
                ' . $this->humanOptions($hTariff, 'max_ftp');
        if (!empty($hTariff['has_shell']) && !empty($hTariff['max_ssh'])) {
            $html .= $this->humanOptions($hTariff, 'max_ssh');
        }
        if (!empty($hTariff->is_ssl_included)) {
            $html .= '<p>Free SSL Let\'s Encrypt <i class="fa fa-check dc-check" aria-hidden="true"></i></p>';
        }
        $html .= '        </ul>
        </div>
                        <p class="prise"> Coûts <span>' . $hTariff['price'] . '/an</span></p>
                        <div class="start_btn text-center">
                        <a data-belongs="' . $this->getBelongsTo($hTariff) . '" data-sku="' . $hTariff['id'] .
            '" data-type="hosting" href="#" class="boxed_btn_green">Je commande</a>

                    </div>
                </div>
                </div>
                </div>';

        return $html;
    }

    private function _planHtml($hTariff)
    {
        $html = '
                <div class="col-xl-3 col-md-6 col-lg-6">
                    <div class="single_prising">
                        <div class="prising_icon blue">
                            <i class="flaticon-servers"></i>
                        </div>
                        <h3>' . Inflector::humanize($hTariff['name']) . '</h3>
                        <p class="prising_text">' . $hTariff['description'] . '</p>
                ' . $this->inclDomains($hTariff) . '
                ' . $this->storage($hTariff, 'ram_quota') . '
                ' . $this->storage($hTariff) . '
                ' . $this->storage($hTariff, 'bwlimit') . '
                ' . $this->humanOptions($hTariff, 'max_subdomain') . '
                ' . $this->humanOptions($hTariff, 'max_pop') . '
                ' . $this->humanOptions($hTariff, 'max_mysql') . '
                ' . $this->humanOptions($hTariff, 'max_ftp');
        if (!empty($hTariff['has_shell']) && !empty($hTariff['max_ssh'])) {
            $html .= $this->humanOptions($hTariff, 'max_ssh');
        }
        if (!empty($hTariff->is_ssl_included)) {
            $html .= '<p>Free SSL Let\'s Encrypt <i class="fa fa-check dc-check" aria-hidden="true"></i></p>';
        }
        $html .= '
                        <p class="prise"> Coûts <span>' . $hTariff['price'] . '/an</span></p>
                        <a data-belongs="' . $this->getBelongsTo($hTariff) . '" data-sku="' . $hTariff['id'] . '" data-type="hosting" href="#" class="add2cart boxed_btn_green2">Je commande</a>
                    </div>
                </div>';

        return $html;
    }

    private function inclDomains(array $hTariffs): string
    {
        $sLI = '<p class="no-hover">&nbsp;</p>';
        if (empty($hTariffs) || empty($hTariffs['is_domain_included'])) {
            return $sLI;
        }

        $sLI = ($hTariffs['nr_domain_included'] > 1) ? sprintf('%d noms de domaine gratuit', $hTariffs['nr_domain_included']) : '1 nom de domaine gratuit';

        return sprintf('<p>%s **</p>', $sLI);
    }

    private function storage(array $hTariffs, ?string $sOption = null): string
    {
        if (empty($hTariffs)) {
            return '';
        }

        if ($sOption === null) {
            if (!empty($hTariffs['disk_quota'])) {
                $sLI = '<p>' . $this->formatBytes($hTariffs['disk_quota']) . 'o Espace disque</p>';
            } else {
                $sLI = '<p>Espace disque illimité</p>';
            }

            return $sLI;
        }
        if (!empty($hTariffs[$sOption])) {
            if ($sOption == 'email_quota') {
                $sLI = '<p>' . $this->formatBytes($hTariffs[$sOption]) . 'o par compte </p>';
            } else {
                $sLI = '<p>' . $this->formatBytes($hTariffs[$sOption]) . 'o ' . $this->_ipr(sprintf('lang_%s', $sOption)) . '</p>';
            }
        } else {
            $sLI = '<p>' . $this->_ipr(sprintf('lang_unlimited_%s', $sOption)) . '</p>';
        }

        return $sLI;
    }

    private function humanOptions(array $hTariffs, string $sOption): string
    {
        if (empty($hTariffs)) {
            return '';
        }

        if (!empty($hTariffs[$sOption]) && $hTariffs[$sOption] < 1000) {
            if ($sOption === 'max_ssh') {
                $sTxt = $hTariffs[$sOption] > 1 ? $this->_ipr(sprintf('lang_%s', $sOption . '_s')) :
                        $this->_ipr(sprintf('lang_%s', $sOption));

                return sprintf('<p>%s</p>', $hTariffs[$sOption] . ' ' . $sTxt);
            }
            $sLI = '<p>' . $hTariffs[$sOption] . ' ' . $this->_ipr(sprintf('lang_%s', $sOption)) . '</p>';
        } else {
            $sLI = '<p>' . $this->_ipr(sprintf('lang_unlimited_%s', $sOption)) . '</p>';
        }

        return $sLI;
    }

    private function formatBytes($size, $precision = 2)
    {
        if (empty($size)) {
            return 0;
        }
        $base = log($size, 1000);
        $suffixes = ['', 'M', 'G', 'T'];

        return round(pow(1000, $base - floor($base)), $precision) . $suffixes[floor($base)];
    }

    private function _ipr(string $key): string
    {
        $ipr = [
            'lang_max_ftp' => 'Comptes FTP',
            'lang_max_ssh' => 'Compte SSH/SFTP',
            'lang_max_ssh_s' => 'Comptes SSH/SFTP',
            'lang_max_mysql' => 'Bases de données MySQL',
            'lang_max_pop' => 'Comptes e-mail',
            'lang_max_pop_list' => 'Mailinglists',
            'lang_max_subdomain' => 'Sous-domaines',
            'lang_bwlimit' => 'Bande passante',
            'lang_unlimited_max_ftp' => 'Comptes FTP illimités',
            'lang_unlimited_max_mysql' => 'Bases de données MySQL illimitées',
            'lang_unlimited_max_pop' => 'Comptes e-mail illimités',
            'lang_unlimited_max_pop_list' => 'Mailinglist illimitée',
            'lang_unlimited_max_subdomain' => 'Sous-domaines illimités',
            'lang_unlimited_bwlimit' => 'Bande passante illimitée',
            'lang_unlimited_ram' => 'RAM illimitée',
            'lang_ram_quota' => 'Ram',
            'lang_unlimited_ram_quota' => 'RAM illimitée',
        ];

        return array_key_exists($key, $ipr) ? $ipr[$key] : $key;
    }

    private function getBelongsTo(array $tariff): ?string
    {
        if (array_key_exists('package_group', $tariff)) {
            return $tariff['package_group']['name'];
        }

        return null;
    }

    private function getTypeName(array $tariff): ?string
    {
        if (array_key_exists('package_type', $tariff)) {
            return $tariff['package_type']['name'];
        }

        return null;
    }
}
