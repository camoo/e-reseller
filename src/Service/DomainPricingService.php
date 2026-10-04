<?php

declare(strict_types=1);

namespace App\Service;

use Camoo\Hosting\Modules\Domains;

final readonly class DomainPricingService
{
    public function __construct(private ?Domains $domains = null)
    {
    }

    /**
     * Fetch the reseller's current domain prices through the official SDK.
     *
     * @param string[] $tlds
     * @return array<string, int|float>
     */
    public function fetch(array $tlds): array
    {
        $tlds = $this->normalizeTlds($tlds);
        if ($tlds === []) {
            return [];
        }

        $domains = $this->domains ?? new Domains();
        $response = $domains->getPrices();
        if ($response->getStatusCode() !== 200) {
            return [];
        }

        return self::extractPrices($response->getJson(), $tlds);
    }

    /**
     * Extract prices from the SDK domain-prices response.
     *
     * @param array<string,mixed> $payload
     * @param string[]             $tlds
     * @return array<string, int|float>
     */
    public static function extractPrices(array $payload, array $tlds): array
    {
        $result = $payload['result'] ?? $payload;
        if (!is_array($result)) {
            return [];
        }

        $allowedTlds = array_fill_keys(self::normalizeTlds($tlds), true);
        $prices = [];
        foreach ($result as $domain => $details) {
            if (!is_array($details)) {
                continue;
            }

            $tld = strtolower(ltrim(trim((string)($details['tld'] ?? $domain)), '.'));
            if ($tld === '' || !isset($allowedTlds[$tld])) {
                continue;
            }

            if (($details['hidden'] ?? false) === true) {
                continue;
            }

            $price = $details['price']['addnewdomain']
                ?? (is_numeric($details['price'] ?? null) ? $details['price'] : null);
            if (is_numeric($price)) {
                $prices[$tld] = $price + 0;
            }
        }

        return $prices;
    }

    /**
     * Apply SDK prices to the homepage's configurable badge collection.
     *
     * @param array<string,mixed>     $homeContent
     * @param array<string,int|float> $prices
     * @param string[]                 $managedTlds
     * @return array<string,mixed>
     */
    public static function applyToHomeContent(array $homeContent, array $prices, array $managedTlds = []): array
    {
        if (!is_array($homeContent['tld_badges'] ?? null)) {
            return $homeContent;
        }

        $managed = array_fill_keys(self::normalizeTlds($managedTlds), true);
        foreach ($homeContent['tld_badges'] as &$badge) {
            if (!is_array($badge)) {
                continue;
            }

            $tld = ltrim(strtolower((string)($badge['name'] ?? '')), '.');
            if ($tld === '' || !isset($managed[$tld])) {
                continue;
            }

            unset($badge['price']);
            if (!array_key_exists($tld, $prices)) {
                continue;
            }

            $badge['price'] = 'XAF ' . number_format((float)$prices[$tld], 0, '.', ',');
        }
        unset($badge);

        return $homeContent;
    }

    /**
     * @param array<string,mixed> $config
     * @param string[]             $tlds
     * @return array<string,int|float>
     */
    public static function localFixturePrices(array $config, array $tlds): array
    {
        $prices = is_array($config['prices'] ?? null) ? $config['prices'] : [];
        $allowedTlds = array_fill_keys(self::normalizeTlds($tlds), true);
        $result = [];
        foreach ($prices as $tld => $price) {
            $tld = strtolower(ltrim((string)$tld, '.'));
            if (isset($allowedTlds[$tld]) && is_numeric($price)) {
                $result[$tld] = $price + 0;
            }
        }

        return $result;
    }

    /** @param array<int|string,mixed> $tlds @return string[] */
    private static function normalizeTlds(array $tlds): array
    {
        $normalized = [];
        foreach ($tlds as $tld) {
            $tld = strtolower(ltrim(trim((string)$tld), '.'));
            if ($tld !== '' && preg_match('/^[a-z0-9]+(?:[.-][a-z0-9]+)*$/', $tld)) {
                $normalized[$tld] = true;
            }
        }

        return array_keys($normalized);
    }
}
