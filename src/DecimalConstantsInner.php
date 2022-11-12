<?php
declare(strict_types=1);

namespace Litipk\BigNumbers;

use Litipk\BigNumbers\DecimalInner as Decimal;


/**
 * Class that holds many important numeric constants
 *
 * @author Andreu Correa Casablanca <castarco@litipk.com>
 */
final class DecimalConstantsInner
{
    /** @var DecimalInner */
    private static $ZERO = null;
    /** @var DecimalInner */
    private static $ONE = null;
    /** @var DecimalInner */
    private static $NEGATIVE_ONE = null;

    /** @var DecimalInner */
    private static $PI = null;
    /** @var DecimalInner */
    private static $EulerMascheroni = null;

    /** @var DecimalInner */
    private static $GoldenRatio = null;
    /** @var DecimalInner */
    private static $SilverRatio = null;
    /** @var DecimalInner */
    private static $LightSpeed = null;

    private function __construct()
    {
    }

    private function __clone()
    {
    }

    public static function zero(): DecimalInner
    {
        if (null === self::$ZERO) {
            self::$ZERO = DecimalInner::fromInteger(0);
        }
        return self::$ZERO;
    }

    public static function one(): DecimalInner
    {
        if (null === self::$ONE) {
            self::$ONE = DecimalInner::fromInteger(1);
        }
        return self::$ONE;
    }

    public static function negativeOne(): DecimalInner
    {
        if (null === self::$NEGATIVE_ONE) {
            self::$NEGATIVE_ONE = DecimalInner::fromInteger(-1);
        }
        return self::$NEGATIVE_ONE;
    }

    /**
     * Returns the Pi number.
     * @return DecimalInner
     */
    public static function pi(): DecimalInner
    {
        if (null === self::$PI) {
            self::$PI = DecimalInner::fromString(
                "3.14159265358979323846264338327950"
            );
        }
        return self::$PI;
    }

    /**
     * Returns the Euler's E number.
     * @param  integer $scale
     * @return DecimalInner
     */
    public static function e(int $scale = 32): DecimalInner
    {
        if ($scale < 0) {
            throw new \InvalidArgumentException("\$scale must be positive.");
        }

        return self::one()->exp($scale);
    }

    /**
     * Returns the Euler-Mascheroni constant.
     * @return DecimalInner
     */
    public static function eulerMascheroni(): DecimalInner
    {
        if (null === self::$EulerMascheroni) {
            self::$EulerMascheroni = DecimalInner::fromString(
                "0.57721566490153286060651209008240"
            );
        }
        return self::$EulerMascheroni;
    }

    /**
     * Returns the Golden Ration, also named Phi.
     * @return DecimalInner
     */
    public static function goldenRatio(): DecimalInner
    {
        if (null === self::$GoldenRatio) {
            self::$GoldenRatio = DecimalInner::fromString(
                "1.61803398874989484820458683436564"
            );
        }
        return self::$GoldenRatio;
    }

    /**
     * Returns the Silver Ratio.
     * @return DecimalInner
     */
    public static function silverRatio(): DecimalInner
    {
        if (null === self::$SilverRatio) {
            self::$SilverRatio = DecimalInner::fromString(
                "2.41421356237309504880168872420970"
            );
        }
        return self::$SilverRatio;
    }

    /**
     * Returns the Light of Speed measured in meters / second.
     * @return DecimalInner
     */
    public static function lightSpeed(): DecimalInner
    {
        if (null === self::$LightSpeed) {
            self::$LightSpeed = DecimalInner::fromInteger(299792458);
        }
        return self::$LightSpeed;
    }
}
