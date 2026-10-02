<?php

declare(strict_types=1);

namespace App\Test;

use App\Service\DomainPricingService;
use PHPUnit\Framework\TestCase;

final class DomainPricingServiceTest extends TestCase
{
    public function testExtractsSdkDomainPricesByTld(): void
    {
        $prices = DomainPricingService::extractPrices([
            'status' => 'OK',
            'result' => [
                'cm' => [
                    'tld' => 'cm',
                    'price' => 6500,
                ],
                'com' => [
                    'tld' => 'com',
                    'price' => '7500',
                ],
                'net' => ['tld' => 'net', 'price' => 8500, 'hidden' => true],
            ],
        ], ['cm', 'com', 'net']);

        self::assertSame(['cm' => 6500, 'com' => 7500], $prices);
    }

    public function testAppliesOnlyReturnedPricesToHomepageBadges(): void
    {
        $homeContent = [
            'tld_badges' => [
                ['name' => '.com'],
                ['name' => '.net', 'price' => 'XAF 8,500'],
                ['name' => 'SSL', 'price' => 'Gratuit'],
            ],
        ];

        $result = DomainPricingService::applyToHomeContent($homeContent, ['com' => 7500], ['com', 'net']);

        self::assertSame('XAF 7,500', $result['tld_badges'][0]['price']);
        self::assertArrayNotHasKey('price', $result['tld_badges'][1]);
        self::assertSame('Gratuit', $result['tld_badges'][2]['price']);
    }
}
