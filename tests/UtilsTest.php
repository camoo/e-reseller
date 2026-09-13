<?php

declare(strict_types=1);

namespace App\Test;

use App\Lib\Utils;
use PHPUnit\Framework\TestCase;

final class UtilsTest extends TestCase
{
    public function testValidatesInternationalPhoneNumbers(): void
    {
        self::assertTrue(Utils::isValidPhoneNumber('+237677123456', 'CM'));
        self::assertTrue(Utils::isValidPhoneNumber('677123456', 'cm', true));
    }

    public function testRejectsEmptyAndMalformedPhoneNumbers(): void
    {
        self::assertNull(Utils::getNumberProto('   ', 'CM'));
        self::assertFalse(Utils::isValidPhoneNumber('not-a-number', 'CM'));
    }
}
