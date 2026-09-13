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
}
