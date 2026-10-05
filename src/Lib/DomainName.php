<?php

declare(strict_types=1);

namespace App\Lib;

final class DomainName
{
    public static function normalize(string $domain): string
    {
        $domain = strtolower(trim($domain));
        $labels = explode('.', trim($domain, '.'));

        return $labels[0] ?? '';
    }

    public static function isValid(string $domain): bool
    {
        $domain = strtolower(trim($domain));
        if ($domain === '' || strlen($domain) > 253) {
            return false;
        }

        $labels = explode('.', trim($domain, '.'));
        if (count($labels) < 2) {
            return (bool)preg_match('/\A(?!-)[a-z0-9-]{2,}(?<!-)\z/', $labels[0]);
        }

        foreach ($labels as $label) {
            if ($label === '' || strlen($label) > 63 || !preg_match('/\A(?!-)[a-z0-9-]+(?<!-)\z/', $label)) {
                return false;
            }
        }

        return (bool)preg_match('/\A[a-z]{2,}\z/', end($labels));
    }

    /**
     * Reorders domain availability results so domains matching the primary TLDs
     * appear first, while preserving the relative ordering of all remaining domains.
     *
     * @param array<string, mixed> $results
     * @param array<string> $primaryTlds
     * @return array<string, mixed>
     */
    public static function sortAvailabilityResults(array $results, array $primaryTlds = ['cm']): array
    {
        if (empty($results) || empty($primaryTlds)) {
            return $results;
        }

        $normalizedPrimaryTlds = array_values(array_filter(array_map(
            static fn (string $tld): string => strtolower(ltrim(trim($tld), '.')),
            $primaryTlds,
        )));

        if (empty($normalizedPrimaryTlds)) {
            return $results;
        }

        $primaryGroups = array_fill_keys(array_keys($normalizedPrimaryTlds), []);
        $others = [];

        foreach ($results as $domain => $data) {
            $domainStr = strtolower(trim((string)$domain));
            $matchedIndex = null;

            foreach ($normalizedPrimaryTlds as $index => $tld) {
                if ($tld === '') {
                    continue;
                }
                if ($domainStr === $tld || str_ends_with($domainStr, '.' . $tld)) {
                    $matchedIndex = $index;
                    break;
                }
            }

            if ($matchedIndex !== null) {
                $primaryGroups[$matchedIndex][$domain] = $data;
            } else {
                $others[$domain] = $data;
            }
        }

        $sorted = [];
        foreach ($primaryGroups as $group) {
            foreach ($group as $domain => $data) {
                $sorted[$domain] = $data;
            }
        }
        foreach ($others as $domain => $data) {
            $sorted[$domain] = $data;
        }

        return $sorted;
    }
}
