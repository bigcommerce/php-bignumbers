<?php

declare(strict_types=1);

namespace Litipk\BigNumbers;

use Psr\Log\LoggerInterface;
use Throwable;

/**
 * Decimal is a wrapper of Litipk\BigNumbers\Decimal which provides the catch-all exception handling.
 *
 * This is a short-term solution to help us identify edge cases that are not handled properly. We should move to a
 * better library to handle big numbers in the future.
 *
 * @method Decimal add(Decimal $b, int $scale = null)
 * @method Decimal sub(Decimal $b, int $scale = null)
 * @method Decimal mul(Decimal $b, int $scale = null)
 * @method Decimal div(Decimal $b, int $scale = null)
 * @method Decimal sqrt(Decimal $b, int $scale = null)
 * @method Decimal pow(Decimal $b, int $scale = null)
 * @method Decimal log10(int $scale = null)
 * @method bool isZero(int $scale = null)
 * @method bool isPositive()
 * @method bool isNegative()
 * @method bool isInteger()
 * @method bool equals(Decimal $b, int $scale = null)
 * @method int comp(Decimal $b, int $scale = null)
 * @method bool isGreaterThan(Decimal $b, int $scale = null)
 * @method bool isGreaterOrEqualTo(Decimal $b, int $scale = null)
 * @method bool isLessThan(Decimal $b, int $scale = null)
 * @method bool isLessOrEqualTo(Decimal $b, int $scale = null)
 * @method Decimal additiveInverse()
 * @method Decimal round(int $scale = 0)
 * @method Decimal ceil(int $scale = 0)
 * @method Decimal floor(int $scale = 0)
 * @method Decimal abs(int $scale = 0)
 * @method Decimal mod(Decimal $d, int $scale = null)
 * @method Decimal sin(int $scale = null)
 * @method Decimal cos(int $scale = null)
 * @method Decimal cosec(int $scale = null)
 * @method Decimal sec(int $scale = null)
 * @method Decimal arcsin(int $scale = null)
 * @method Decimal arccos(int $scale = null)
 * @method Decimal arctan(int $scale = null)
 * @method Decimal arccot(int $scale = null)
 * @method Decimal arcsec(int $scale = null)
 * @method Decimal arccsc(int $scale = null)
 * @method Decimal exp(int $scale = null)
 * @method Decimal cotan(int $scale = null)
 * @method bool hasSameSign(Decimal $b)
 * @method float asFloat()
 * @method int asInteger()
 * @method string innerValue()
 */
class Decimal
{
    public const DEFAULT_SCALE = DecimalInner::DEFAULT_SCALE;
    public const CLASSIC_DECIMAL_NUMBER_REGEXP = DecimalInner::CLASSIC_DECIMAL_NUMBER_REGEXP;
    public const EXP_NOTATION_NUMBER_REGEXP = DecimalInner::EXP_NOTATION_NUMBER_REGEXP;
    public const EXP_NUM_GROUPS_NUMBER_REGEXP = DecimalInner::EXP_NUM_GROUPS_NUMBER_REGEXP;

    private DecimalInner $decimal;

    private static ?LoggerInterface $logger = null;

    public function __construct(DecimalInner $decimal)
    {
        $this->decimal = $decimal;
    }

    public static function setLogger(?LoggerInterface $logger = null): void
    {
        self::$logger = $logger;
    }

    public function getDecimal(): DecimalInner
    {
        return $this->decimal;
    }

    /**
     * Note: explicit type hinting is needed so auto type casting is allowed when the caller doesn't have strict
     * type checking
     * @param string $value
     * @param int|null $scale
     * @return Decimal
     */
    public static function fromString(string $value, int $scale = null): Decimal
    {
        return self::__callStatic(__FUNCTION__, [$value, $scale]);
    }

    public static function fromFloat(float $value, int $scale = null): Decimal
    {
        return self::__callStatic(__FUNCTION__, [$value, $scale]);
    }

    public static function fromInteger(int $value): Decimal
    {
        return self::__callStatic(__FUNCTION__, [$value]);
    }

    public static function fromDecimal(Decimal $decValue, int $scale = null): Decimal
    {
        return self::__callStatic(__FUNCTION__, [$decValue, $scale]);
    }

    /**
     * @param mixed $value
     * @param int $scale
     * @return Decimal
     */
    public static function create($value, int $scale = null): Decimal
    {
        return self::__callStatic(__FUNCTION__, [$value, $scale]);
    }

    /**
     * @param string $name
     * @param array $arguments
     * @return Decimal|mixed
     * @throws Throwable
     */
    public static function __callStatic(string $name, array $arguments)
    {
        try {
            $arguments = self::unwrapArguments($arguments);
            $result = DecimalInner::$name(...$arguments);

            return self::maybeWrapResult($result);
        } catch (Throwable $e) {
            self::logError("Decimal::$name() failed with exception: {$e->getMessage()}", [
                'arguments' => $arguments,
                'exception' => get_class($e),
            ]);

            throw $e;
        }
    }

    /**
     * @param string $name
     * @param array $arguments
     * @return Decimal|mixed
     * @throws Throwable
     */
    public function __call(string $name, array $arguments)
    {
        try {
            $arguments = self::unwrapArguments($arguments);
            $result = $this->decimal->$name(...$arguments);
            return self::maybeWrapResult($result);
        } catch (Throwable $e) {
            self::logError("Decimal::$name() failed with exception: {$e->getMessage()}", [
                'subject' => (string)$this,
                'arguments' => $arguments,
                'exception' => get_class($e),
            ]);

            throw $e;
        }
    }

    private static function unwrapArguments(array $arguments = []): array
    {
        // Use the original DecimalInner object for the method call
        foreach ($arguments as &$arg) {
            if ($arg instanceof self) {
                $arg = $arg->getDecimal();
            }
        }

        return $arguments;
    }

    /**
     * We don't need this but phpstan complains if we don't have it.
     * @return string
     */
    public function __toString(): string
    {
        return $this->decimal->__toString();
    }

    /**
     * @param mixed $result
     * @return Decimal|mixed
     */
    private static function maybeWrapResult($result)
    {
        return $result instanceof DecimalInner ? new self($result) : $result;
    }

    private static function logError(string $message, array $context = []): void
    {
        if (!self::$logger) {
            return;
        }
        self::$logger->error($message, $context);
    }
}
