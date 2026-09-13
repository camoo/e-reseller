<?php

declare(strict_types=1);

namespace App\Lib;

use libphonenumber\PhoneNumberUtil;
use libphonenumber\PhoneNumber;
use libphonenumber\PhoneNumberType;

/**
 * Class Utils
 *
 * @author CamooSarl
 */
class Utils
{
    public static function phoneUtil(): PhoneNumberUtil
    {
        return PhoneNumberUtil::getInstance();
    }

    public static function getNumberProto(string $xTel, ?string $sCcode = null): ?PhoneNumber
    {
        $xTel = trim($xTel);
        if ($xTel !== '') {
            try {
                return self::phoneUtil()->parse($xTel, $sCcode);
            } catch (\libphonenumber\NumberParseException) {
                return null;
            }
        }

        return null;
    }

    public static function isValidPhoneNumber(string $xTel, string $sCcode, ?bool $bStrict = null): bool
    {
        $sCcode = strtoupper(trim($sCcode));
        $bRet = ($oNumberProto = self::getNumberProto($xTel, $sCcode)) instanceof \libphonenumber\PhoneNumber
            && self::phoneUtil()->isValidNumber($oNumberProto)
            && self::phoneUtil()->getNumberType($oNumberProto) !== PhoneNumberType::UNKNOWN;
        if ($bRet && $bStrict === true) {
            return self::phoneUtil()->getRegionCodeForNumber($oNumberProto) === $sCcode;
        }

        return $bRet;
    }
}
