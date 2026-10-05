<?php

declare(strict_types=1);

namespace App\Test;

use App\Lib\DomainName;
use PHPUnit\Framework\TestCase;

final class DomainNameTest extends TestCase
{
    public function testNormalizeReturnsTheHostLabelUsedForAvailabilitySearch(): void
    {
        self::assertSame('example', DomainName::normalize('  Example.COM. '));
    }

    public function testValidatesSingleLabelsAndFullyQualifiedDomains(): void
    {
        self::assertTrue(DomainName::isValid('example.com'));
        self::assertTrue(DomainName::isValid('hosting'));
        self::assertFalse(DomainName::isValid('-example.com'));
        self::assertFalse(DomainName::isValid('example.invalid_tld'));
    }

    public function testSortAvailabilityResultsPlacesCmAtTheTopByDefault(): void
    {
        $input = [
            'jawleeru.biz' => ['status' => 'Y', 'price' => ['addnewdomain' => 13785]],
            'jawleeru.org' => ['status' => 'Y', 'price' => ['addnewdomain' => 10000]],
            'jawleeru.info' => ['status' => 'Y', 'price' => ['addnewdomain' => 14565]],
            'jawleeru.cm' => ['status' => 'N', 'price' => ['addnewdomain' => 7000]],
            'jawleeru.com' => ['status' => 'Y', 'price' => ['addnewdomain' => 9990]],
            'jawleeru.net' => ['status' => 'Y', 'price' => ['addnewdomain' => 9630]],
            'jawleeru.pro' => ['status' => 'Y', 'price' => ['addnewdomain' => 9825]],
        ];

        $sorted = DomainName::sortAvailabilityResults($input);

        $expectedKeys = [
            'jawleeru.cm',
            'jawleeru.biz',
            'jawleeru.org',
            'jawleeru.info',
            'jawleeru.com',
            'jawleeru.net',
            'jawleeru.pro',
        ];

        self::assertSame($expectedKeys, array_keys($sorted));
        self::assertSame('N', $sorted['jawleeru.cm']['status']);
        self::assertSame(7000, $sorted['jawleeru.cm']['price']['addnewdomain']);
    }

    public function testSortAvailabilityResultsSupportsConfiguredMultiplePrimaryTlds(): void
    {
        $input = [
            'example.net' => ['status' => 'Y'],
            'example.biz' => ['status' => 'Y'],
            'example.org' => ['status' => 'Y'],
            'example.com' => ['status' => 'Y'],
            'example.cm' => ['status' => 'N'],
        ];

        $sorted = DomainName::sortAvailabilityResults($input, ['.cm', 'com']);

        $expectedKeys = [
            'example.cm',
            'example.com',
            'example.net',
            'example.biz',
            'example.org',
        ];

        self::assertSame($expectedKeys, array_keys($sorted));
    }

    public function testSortAvailabilityResultsWithEmptyInputOrTlds(): void
    {
        self::assertSame([], DomainName::sortAvailabilityResults([]));

        $input = ['example.com' => ['status' => 'Y']];
        self::assertSame($input, DomainName::sortAvailabilityResults($input, []));
    }
}
