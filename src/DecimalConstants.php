<?php

declare(strict_types=1);

namespace Litipk\BigNumbers;

class DecimalConstants
{
    /** @var Decimal[] */
    private static $constants = [];

    public static function zero(): Decimal
    {
        return self::getConstant(__FUNCTION__);
    }

    public static function one(): Decimal
    {
        return self::getConstant(__FUNCTION__);
    }

    public static function negativeOne(): Decimal
    {
        return self::getConstant(__FUNCTION__);
    }

    public static function pi(): Decimal
    {
        return self::getConstant(__FUNCTION__);
    }

    public static function eulerMascheroni(): Decimal
    {
        return self::getConstant(__FUNCTION__);
    }

    public static function goldenRatio(): Decimal
    {
        return self::getConstant(__FUNCTION__);
    }

    public static function silverRatio(): Decimal
    {
        return self::getConstant(__FUNCTION__);
    }

    public static function lightSpeed(): Decimal
    {
        return self::getConstant(__FUNCTION__);
    }

    public static function e(int $scale = null): Decimal
    {
        return self::one()->exp($scale);
    }

    private static function getConstant(string $name): Decimal
    {
        if (!isset(self::$constants[$name])) {
            self::$constants[$name] = new Decimal(DecimalConstantsInner::$name());
        }
        return self::$constants[$name];
    }
}
