<?php

declare(strict_types=1);

namespace App\Template\Extension\Functions;

use Camoo\Inflector\Inflector;
use CAMOO\Template\Extension\FunctionHelper;
use CAMOO\Utils\Configure;

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
            $this->add('hosting_plans', $this->getHostingPlans(...), ['is_safe' => ['html']]),
            $this->add('package_plans', $this->getPackagePlans(...), ['is_safe' => ['html']]),
            $this->add('product_plans', $this->getProductPlans(...), ['is_safe' => ['html']]),
        ];
    }

    public function getHostingPlans(): string
    {
        $html = '';
        if (($ahTariffs = $this->getTariffs()) !== []) {
            $count = 0;
            foreach ($ahTariffs as $ahTariff) {
                if ($this->isHostingTariff($ahTariff)) {
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
        if (($ahTariffs = $this->getTariffs()) !== []) {
            $count = 0;
            foreach ($ahTariffs as $ahTariff) {
                if ($this->isHostingTariff($ahTariff)) {
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

    /**
     * Render non-shared-hosting products from the tariff catalog returned by
     * the hosting API. The API owns names, descriptions, prices and limits;
     * this extension only selects the relevant product type for the page.
     */
    public function getProductPlans(string $product): string
    {
        $headerOptions = ['', 'deep', 'yellow'];
        $html = '';
        $count = 0;

        foreach ($this->getTariffs() as $tariff) {
            if (!$this->isProductTariff($tariff, $product)) {
                continue;
            }

            $html .= $this->generateProductHtml($tariff, $headerOptions[$count % count($headerOptions)]);
            $count++;
        }

        return $html;
    }

    /** @return array<int, array<string, mixed>> */
    private function getTariffs(): array
    {
        $all = Configure::read('RESELLER_TARIFFS');
        if (!is_array($all) || !is_array($all['tariffs'] ?? null)) {
            return [];
        }

        return array_values(array_filter($all['tariffs'], 'is_array'));
    }

    /** @param array<string, mixed> $tariff */
    private function isHostingTariff(array $tariff): bool
    {
        $packageType = $tariff['package_type'] ?? null;

        return is_array($packageType)
            ? (int)($packageType['id'] ?? 0) === 2
            : (int)($tariff['package_type_id'] ?? 0) === 2;
    }

    /** @param array<string, mixed> $tariff */
    private function isProductTariff(array $tariff, string $product): bool
    {
        $product = strtolower(trim($product));
        if ($product === 'hosting') {
            return $this->isHostingTariff($tariff);
        }

        $aliases = [
            'vps' => ['vps', 'server', 'servers', 'virtual private server', 'dedicated'],
            'emails' => ['email', 'emails', 'e-mail', 'mail', 'mailbox'],
            'ssl' => ['ssl', 'certificate', 'certificates', 'https'],
        ];
        if (!array_key_exists($product, $aliases)) {
            return false;
        }

        // The API normally includes package_type.name. Keep the numeric
        // identifiers as a fallback for older API responses that only return
        // package_type.id (hosting itself remains the existing type 2).
        $productTypeIds = [
            'vps' => [3, 6],
            'emails' => [4, 5, 11, 12, 13],
            'ssl' => [7],
        ];
        $packageType = $tariff['package_type'] ?? null;
        $packageTypeId = is_array($packageType)
            ? (int)($packageType['id'] ?? 0)
            : (int)($tariff['package_type_id'] ?? 0);
        if (in_array($packageTypeId, $productTypeIds[$product], true)) {
            return true;
        }

        $values = [];
        foreach (['package_type', 'product_type', 'type', 'category'] as $field) {
            $value = $tariff[$field] ?? null;
            if (is_array($value)) {
                foreach (['slug', 'code', 'name', 'label', 'title'] as $nestedField) {
                    if (isset($value[$nestedField])) {
                        $values[] = (string)$value[$nestedField];
                    }
                }
                continue;
            }
            if (is_string($value)) {
                $values[] = $value;
            }
        }

        // Some API responses expose the product name only on the tariff.
        // Descriptions are deliberately excluded because shared-hosting
        // plans commonly mention included email or SSL features.
        foreach (['name'] as $field) {
            if (isset($tariff[$field])) {
                $values[] = (string)$tariff[$field];
            }
        }

        foreach ($values as $value) {
            $normalized = strtolower(trim((string)preg_replace('/[^a-z0-9]+/i', ' ', $value)));
            foreach ($aliases[$product] as $alias) {
                if ($normalized === $alias || str_contains($normalized, $alias)) {
                    return true;
                }
            }
        }

        return false;
    }

    /** @param array<string, mixed> $tariff */
    private function generateProductHtml(array $tariff, string $headerClass): string
    {
        $description = $tariff['desc_short'] ?? $tariff['description'] ?? '';
        $html = '
                <div class="col-xl-4 col-md-6 col-lg-4">
                    <div class="single_prising">
                        <div class="prising_header ' . $this->escape($headerClass) . '">
                            <h3>' . $this->escape(Inflector::humanize((string)($tariff['name'] ?? ''))) . '</h3>
                        </div>
                        <div class="middle_content">
                            <div class="list">
                                <ul>';

        if (trim((string)$description) !== '') {
            $html .= '<li>' . $this->escape((string)$description) . '</li>';
        }

        $html .= $this->productFeatureRows($tariff);
        $html .= '</ul>
                            </div>
                        </div>
                        <p class="prise"> Coûts <span>' . $this->escape((string)($tariff['price'] ?? '')) . '/an</span></p>
                        <div class="start_btn text-center">
                            <a data-belongs="' . $this->escape((string)$this->getBelongsTo($tariff)) . '" data-sku="' . $this->escape((string)($tariff['id'] ?? '')) . '" data-type="hosting" href="#" class="add2cart boxed_btn_green">Je commande</a>
                        </div>
                    </div>
                </div>';

        return $html;
    }

    /** @param array<string, mixed> $tariff */
    private function productFeatureRows(array $tariff): string
    {
        $html = '';
        foreach (['disk_quota', 'ram_quota', 'bwlimit', 'email_quota'] as $field) {
            if (array_key_exists($field, $tariff)) {
                $html .= $field === 'disk_quota'
                    ? $this->storage($tariff)
                    : $this->storage($tariff, $field);
            }
        }
        foreach (['max_subdomain', 'max_pop', 'max_mysql', 'max_ftp'] as $field) {
            if (array_key_exists($field, $tariff)) {
                $html .= $this->humanOptions($tariff, $field);
            }
        }
        if (!empty($tariff['has_shell']) && array_key_exists('max_ssh', $tariff)) {
            $html .= $this->humanOptions($tariff, 'max_ssh');
        }
        if (!empty($tariff['is_ssl_included'])) {
            $html .= '<p>Free SSL Let\'s Encrypt <i class="fa fa-check dc-check" aria-hidden="true"></i></p>';
        }

        return $html;
    }

    private function generatePackageHtml(array $hTariff, string $headerClass): string
    {
        $html = '
                <div class="col-xl-4 col-md-6 col-lg-4"">
                    <div class="single_prising">
                        <div class="prising_header ' . $headerClass . '">
                            <h3>' . $this->escape(Inflector::humanize((string)$hTariff['name'])) . '</h3>
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

        return $html . ('        </ul>
        </div>
                        <p class="prise"> Coûts <span>' . $this->escape((string)$hTariff['price']) . '/an</span></p>
                        <div class="start_btn text-center">
                        <a data-belongs="' . $this->escape((string)$this->getBelongsTo($hTariff)) . '" data-sku="' . $this->escape((string)$hTariff['id']) . '" data-type="hosting" href="#" class="add2cart boxed_btn_green">Je commande</a>

                    </div>
                </div>
                </div>
                </div>');
    }

    private function _planHtml(array $hTariff): string
    {
        $html = '
                <div class="col-xl-3 col-md-6 col-lg-6">
                    <div class="single_prising">
                        <div class="prising_icon blue">
                            <i class="flaticon-servers"></i>
                        </div>
                        <h3>' . $this->escape(Inflector::humanize((string)$hTariff['name'])) . '</h3>
                        <p class="prising_text">' . $this->escape((string)$hTariff['description']) . '</p>
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

        return $html . ('
                        <p class="prise"> Coûts <span>' . $this->escape((string)$hTariff['price']) . '/an</span></p>
                        <a data-belongs="' . $this->escape((string)$this->getBelongsTo($hTariff)) . '" data-sku="' . $this->escape((string)$hTariff['id']) . '" data-type="hosting" href="#" class="add2cart boxed_btn_green2">Je commande</a>
                    </div>
                </div>');
    }

    private function inclDomains(array $hTariffs): string
    {
        $sLI = '<p class="no-hover">&nbsp;</p>';
        if ($hTariffs === [] || empty($hTariffs['is_domain_included'])) {
            return $sLI;
        }

        $sLI = ($hTariffs['nr_domain_included'] > 1) ? sprintf(
            '%d noms de domaine gratuit',
            $hTariffs['nr_domain_included']
        ) : '1 nom de domaine gratuit';

        return sprintf('<p>%s **</p>', $sLI);
    }

    private function storage(array $hTariffs, ?string $sOption = null): string
    {
        if ($hTariffs === []) {
            return '';
        }

        if ($sOption === null) {
            if (!empty($hTariffs['disk_quota'])) {
                return '<p>' . $this->formatBytes($hTariffs['disk_quota']) . 'o Espace disque</p>';
            }

            return '<p>Espace disque illimité</p>';
        }

        if (!empty($hTariffs[$sOption])) {
            if ($sOption === 'email_quota') {
                $sLI = '<p>' . $this->formatBytes($hTariffs[$sOption]) . 'o par compte </p>';
            } else {
                $sLI = '<p>' . $this->formatBytes($hTariffs[$sOption]) . 'o ' .
                    $this->_ipr(sprintf('lang_%s', $sOption)) . '</p>';
            }
        } else {
            $sLI = '<p>' . $this->_ipr(sprintf('lang_unlimited_%s', $sOption)) . '</p>';
        }

        return $sLI;
    }

    private function humanOptions(array $hTariffs, string $sOption): string
    {
        if ($hTariffs === []) {
            return '';
        }

        if (!empty($hTariffs[$sOption]) && $hTariffs[$sOption] < 1000) {
            if ($sOption === 'max_ssh') {
                $sTxt = $hTariffs[$sOption] > 1 ? $this->_ipr(sprintf('lang_%s', $sOption . '_s')) :
                        $this->_ipr(sprintf('lang_%s', $sOption));

                return sprintf('<p>%s</p>', $hTariffs[$sOption] . ' ' . $sTxt);
            }

            return '<p>' . $hTariffs[$sOption] . ' ' . $this->_ipr(sprintf('lang_%s', $sOption)) . '</p>';
        }

        return '<p>' . $this->_ipr(sprintf('lang_unlimited_%s', $sOption)) . '</p>';
    }

    private function formatBytes(float $size): string
    {
        if (empty($size)) {
            return 0;
        }

        $base = log($size, 1000);
        $suffixes = ['', 'M', 'G', 'T'];

        return round(1000 ** ($base - floor($base)), 2) . $suffixes[floor($base)];
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
            'lang_unlimited_max_pop_list' => 'Mailing list illimitée',
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
        $packageGroup = $tariff['package_group'] ?? null;
        if (is_array($packageGroup) && isset($packageGroup['name'])) {
            return (string)$packageGroup['name'];
        }

        return null;
    }

    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
