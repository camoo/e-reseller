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
}
