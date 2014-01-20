<?php

namespace Litipk\BigNumbers;

use Litipk\BigNumbers\BigNumber as BigNumber;
use Litipk\BigNumbers\IComparableNumber as IComparableNumber;
use Litipk\BigNumbers\AbelianAdditiveGroup as AbelianAdditiveGroup;
use Litipk\BigNumbers\NaN as NaN;
use Litipk\BigNumbers\Infinite as Infinite;
use Litipk\Exceptions\NotImplementedException as NotImplementedException;
use Litipk\Exceptions\InvalidArgumentTypeException as InvalidArgumentTypeException;

/**
 * Immutable object that represents a rational number
 *
 * @author Andreu Correa Casablanca <castarco@litipk.com>
 */
final class Decimal implements BigNumber, IComparableNumber, AbelianAdditiveGroup
{
    /**
     * Internal numeric value
     * @var string
     */
    private $value;

    /**
     * Number of digits behind the point
     * @var integer
     */
    private $scale;

    /**
     * Private constructor
     */
    private function __construct()
    {

    }

    /**
     * Private clone method
     */
    private function __clone()
    {

    }

    /**
     * Decimal "constructor".
     *
     * @param mixed   $value
     * @param integer $scale
     */
    public static function create($value, $scale = null)
    {
        if (is_int(($value))) {
            return self::fromInteger($value, $scale);
        } elseif (is_float($value)) {
            return self::fromFloat($value, $scale);
        } elseif (is_string($value)) {
            return self::fromString($value, $scale);
        } elseif ($value instanceof Decimal) {
            return self::fromDecimal($value, $scale);
        } else {
            throw new InvalidArgumentTypeException(
                array('int', 'float', 'string', 'Decimal'),
                is_object($value) ? get_class($value) : gettype($value),
                'Invalid argument type.'
            );
        }
    }

    /**
     * @param  integer $intValue
     * @param  integer $scale
     * @return Decimal
     */
    public static function fromInteger($intValue, $scale = null)
    {
        self::internalConstructorValidation($intValue, $scale);

        if (!is_int($intValue)) {
            throw new InvalidArgumentTypeException(
                array('int'),
                is_object($intValue) ? get_class($intValue) : gettype($intValue),
                '$intValue must be of type int'
            );
        }

        $decimal = new Decimal();

        $decimal->scale = $scale === null ? 0 : $scale;
        $decimal->value = $scale === null ?
            (string)$intValue : bcadd((string)$intValue, '0', $scale);

        return $decimal;
    }

    /**
     * @param  float   $fltValue
     * @param  integer $scale
     * @return Decimal
     */
    public static function fromFloat($fltValue, $scale = null)
    {
        self::internalConstructorValidation($fltValue, $scale);

        if (!is_float($fltValue)) {
            throw new InvalidArgumentTypeException(
                array('float'),
                is_object($fltValue) ? get_class($fltValue) : gettype($fltValue),
                '$fltValue must be of type float'
            );
        }

        if ($fltValue === INF) {
            return Infinite::getPositiveInfinite();
        } elseif ($fltValue === -INF) {
            return Infinite::getNegativeInfinite();
        } elseif (is_nan($fltValue)) {
            return NaN::getNaN();
        }

        $decimal = new Decimal();

        $decimal->value = number_format($fltValue, $scale === null ? 8 : $scale, '.', '');
        $decimal->scale = $scale === null ? 8 : $scale;

        return $decimal;
    }

    /**
     * @param  string  $strValue
     * @param  integer $scale
     * @return Decimal
     */
    public static function fromString($strValue, $scale = null)
    {
        self::internalConstructorValidation($strValue, $scale);

        if (!is_string($strValue)) {
            throw new InvalidArgumentTypeException(
                array('string'),
                is_object($strValue) ? get_class($strValue) : gettype($strValue),
                '$strVlue must be of type string'
            );
        }

        if (preg_match('/^([+\-]?)0*(([1-9][0-9]*|[0-9])(\.[0-9]+)?)$/', $strValue, $captures) === 1) {

            // Now it's time to strip leading zeros in order to normalize inner values
            $sign      = ($captures[1]==='') ? '+' : $captures[1];
            $value     =  $captures[2];

            $dec_scale = $scale !== null ?
                $scale :
                (isset($captures[4]) ? max(0, strlen($captures[4])-1) : 0);

        } elseif (preg_match('/([+\-]?)([0-9](\.[0-9]+)?)[eE]([+\-]?)([1-9][0-9]*)/', $strValue, $captures) === 1) {

            // Now it's time to "unroll" the exponential notation to basic positional notation
            $sign     = ($captures[1]==='') ? '+' : $captures[1];
            $mantissa = $captures[2];

            $mantissa_scale = strlen($captures[3]) > 0 ? strlen($captures[3])-1 : 0;

            $exp_sign = ($captures[4]==='') ? '+' : $captures[4];
            $exp_val  = (int)$captures[5];

            if ($exp_sign === '+') {
                $min_scale      = ($mantissa_scale-$exp_val > 0) ? $mantissa_scale-$exp_val : 0;
                $tmp_multiplier = bcpow(10, $exp_val);
            } else {
                $min_scale      = $mantissa_scale + $exp_val;
                $tmp_multiplier = bcpow(10, -$exp_val, $exp_val);
            }

            $value     = bcmul($mantissa, $tmp_multiplier, max($min_scale, $scale !== null ? $scale : 0));
            $dec_scale = $scale !== null ? $scale : $min_scale;

        } else {
            throw new \InvalidArgumentException(
                '$strValue must be a string that represents uniquely a float point number'
            );
        }

        if ($sign === '-') {
            $value = '-'.$value;
        }

        if ($scale !== null) {
            $value = self::innerRound($value, $scale);
        }

        $decimal = new Decimal();

        $decimal->value = $value;
        $decimal->scale = $dec_scale;

        return $decimal;
    }

    /**
     * Constructs a new Decimal object based on a previous one,
     * but changing it's $scale property.
     *
     * @param  Decimal  $decValue
     * @param  integer  $scale
     * @return Decimal
     */
    public static function fromDecimal(Decimal $decValue, $scale = null)
    {
        self::internalConstructorValidation($decValue, $scale);

        // This block protect us from unnecessary additional instances
        if ($scale === null || $scale === $decValue->scale) {
            return $decValue;
        }

        $decimal = new Decimal();

        $decimal->value = self::innerRound($decValue->value, $scale);
        $decimal->scale = $scale;

        return $decimal;
    }

    /**
     * Adds two Decimal objects
     * @param  BigNumber $b
     * @param  integer $scale
     * @return BigNumber
     */
    public function add(BigNumber $b, $scale = null)
    {
        self::internalOperatorValidation($b, $scale);

        if ($b instanceof Decimal) {
            return self::fromString(bcadd($this->value, $b->value, max($this->scale, $b->scale)), $scale);
        } else {
            // Hack to support new unknown classes. We use the commutative property
            return $b->add($this);
        }
    }

    /**
     * Subtracts two BigNumber objects
     * @param  BigNumber $b
     * @param  integer $scale
     * @return BigNumber
     */
    public function sub(BigNumber $b, $scale = null)
    {
        self::internalOperatorValidation($b, $scale);

        if ($b->isNaN()) {
            return $b;
        } elseif ($b->isInfinite() && $b->isPositive()) {
            return Infinite::getNegativeInfinite();
        } elseif ($b->isInfinite() && $b->isNegative()) {
            return Infinite::getPositiveInfinite();
        } elseif ($b instanceof Decimal) {
            if ($this->equals($b, $scale)) {
                return self::fromInteger(
                    0,
                    $scale !== null ? $scale : max($this->scale, $b->scale)
                );
            }

            return self::fromString(bcsub($this->value, $b->value, max($this->scale, $b->scale)), $scale);
        } else {
            if ($b instanceof AbelianAdditiveGroup) {

                if ($this->isZero()) {
                    return $b->additiveInverse();
                } else {
                    // Hack to support new unknown classes.
                    return $b->additiveInverse()->add($this);
                }
            }

            throw new NotImplementedException("Decimal has no way to substract an object of type ".get_class($b));
        }
    }

    /**
     * Multiplies two BigNumber objects
     * @param  BigNumber $b
     * @param  integer $scale
     * @return BigNumber
     */
    public function mul(BigNumber $b, $scale = null)
    {
        self::internalOperatorValidation($b, $scale);

        if ($b instanceof Decimal) {
            return self::fromString(bcmul($this->value, $b->value, $this->scale + $b->scale), $scale);
        } else {
            // Hack to support new unknown classes. We use the commutative property
            return $b->mul($this);
        }
    }

    /**
     * Divides the object by $b .
     * Warning: div with $scale == 0 is not the same as
     *          integer division because it rounds the
     *          last digit in order to minimize the error.
     *
     * @param  BigNumber $b
     * @param  integer $scale
     * @return BigNumber
     */
    public function div(BigNumber $b, $scale = null)
    {
        self::internalOperatorValidation($b, $scale);

        if ($b->isNaN()) {
            return $b;
        } elseif ($b->isZero()) {
            return NaN::getNaN();
        } elseif ($this->isZero()) {
            return self::fromDecimal($this, $scale);
        } elseif ($b->isInfinite()) {
            return self::fromInteger(
                0,
                $scale !== null ? $scale : $this->scale
            );
        } elseif ($b instanceof Decimal) {

            if ($scale !== null) {
                $divscale = $scale + 1;
            } else {
                // $divscale is calculated in order to maintain a reasonable precision
                $one      = Decimal::fromInteger(1);
                $this_abs = $this->abs();
                $b_abs    = $b->abs();

                $this_significative_digits = strlen($this->value) - (
                        ($this_abs->comp($one) === -1) ? 2 : ($this->scale > 0 ? 1 : 0)
                    ) - ($this->isNegative() ? 1 : 0);

                $b_significative_digits = strlen($b->value) - (
                        ($b_abs->comp($one) === -1) ? 2 : ($b->scale > 0 ? 1 : 0)
                    ) - ($b->isNegative() ? 1 : 0);

                $log10_result =
                    self::innerLog10($this_abs->value, $this_abs->scale, 1) -
                    self::innerLog10($b_abs->value, $b_abs->scale, 1);

                $divscale = max(
                    $this->scale + $b->scale,
                    max(
                        $this_significative_digits,
                        $b_significative_digits
                    ) - ($log10_result >= 0 ? intval(ceil($log10_result)) : 0),
                    ($log10_result < 0 ? intval(ceil(-$log10_result)) : 0)
                ) + 1;
            }

            return self::fromString(
                bcdiv($this->value, $b->value, $divscale),
                $divscale-1
            );
        } else {
            throw new NotImplementedException("Decimal has no way to divide by an object of type ".get_class($b));
        }
    }

    /**
     * Returns the square root of this object
     * @param  integer $scale
     * @return Decimal
     */
    public function sqrt($scale = null)
    {
        if ($this->isNegative()) {
            return NaN::getNaN();
        } elseif ($this->isZero()) {
            return Decimal::fromDecimal($this, $scale);
        }

        $sqrt_scale = ($scale !== null ? $scale : $this->scale);

        return self::fromString(
            bcsqrt($this->value, $sqrt_scale+1),
            $sqrt_scale
        );
    }

    /**
     * Powers this value to $b
     *
     * @param  Decimal  $b      exponent
     * @param  integer  $scale
     * @return Decimal
     */
    public function pow(Decimal $b, $scale = null)
    {
        if ($this->isZero()) {
            if ($b->isPositive()) {
                return Decimal::fromDecimal($this, $scale);
            } else {
                return NaN::getNaN();
            }
        } elseif ($b->isZero()) {
            return Decimal::fromInteger(1, $scale);
        } elseif ($b->scale == 0) {
            $pow_scale = $scale === null ?
                max($this->scale, $b->scale) : max($this->scale, $b->scale, $scale);

            return self::fromString(
                bcpow($this->value, $b->value, $pow_scale+1),
                $pow_scale
            );
        } else {
            if ($this->isPositive()) {
                $pow_scale = $scale === null ?
                    max($this->scale, $b->scale) : max($this->scale, $b->scale, $scale);

                $truncated_b = bcadd($b->value, '0', 0);
                $remaining_b = bcsub($b->value, $truncated_b, $b->scale);

                $first_pow_approx = bcpow($this->value, $truncated_b, $pow_scale+1);
                $intermediate_root = self::innerPowWithLittleExponent(
                    $this->value,
                    $remaining_b,
                    $b->scale,
                    $pow_scale+1
                );

                return Decimal::fromString(
                    bcmul($first_pow_approx, $intermediate_root, $pow_scale+1),
                    $pow_scale
                );
            } else { // elseif ($this->isNegative())
                return NaN::getNaN();
            }
        }
    }

    /**
     * Returns the object's logarithm in base 10
     * @param  integer $scale
     * @return Decimal
     */
    public function log10($scale = null)
    {
        if ($this->isNegative()) {
            return NaN::getNaN();
        } elseif ($this->isZero()) {
            return Infinite::getNegativeInfinite();
        }

        return self::fromString(
            self::innerLog10($this->value, $this->scale, $scale !== null ? $scale+1 : $this->scale+1),
            $scale
        );
    }

    /**
     * @return boolean
     */
    public function isZero($scale = null)
    {
        $cmp_scale = $scale !== null ? $scale : $this->scale;

        return (bccomp(self::innerRound($this->value, $cmp_scale), '0', $cmp_scale) === 0);
    }

    /**
     * @return boolean
     */
    public function isPositive()
    {
        return ($this->value[0] !== '-' && !$this->isZero());
    }

    /**
     * @return boolean
     */
    public function isNegative()
    {
        return ($this->value[0] === '-');
    }

    /**
     * @return boolean
     */
    public function isInfinite()
    {
        return false;
    }

    /**
     * Says if this object is a "Not a Number"
     * @return boolean
     */
    public function isNaN()
    {
        return false;
    }

    /**
     * Equality comparison between this object and $b
     * @param  BigNumber $b
     * @param integer $scale
     * @return boolean
     */
    public function equals(BigNumber $b, $scale = null)
    {
        self::internalOperatorValidation($b, $scale);

        if ($this === $b) {
            return true;
        } elseif ($b instanceof Decimal) {
            $cmp_scale = $scale !== null ? $scale : max($this->scale, $b->scale);

            return (
                bccomp(
                    self::innerRound($this->value, $cmp_scale),
                    self::innerRound($b->value, $cmp_scale),
                    $cmp_scale
                ) == 0
            );
        } else {
            return $b->equals($this);
        }
    }

    /**
     * $this > $b : returns 1 , $this < $b : returns -1 , $this == $b : returns 0
     *
     * @param  IComparableNumber $b
     * @return integer
     */
    public function comp(IComparableNumber $b, $scale = null)
    {
        self::internalOperatorValidation($b, $scale);

        if ($b === $this) {
            return 0;
        } elseif ($b instanceof Decimal) {
            return bccomp(self::innerRound($this->value, $scale), self::innerRound($b->value, $scale), $scale);
        } else {
            return -$b->comp($this);
        }
    }

    /**
     * Returns the element's additive inverse.
     * @return Decimal
     */
    public function additiveInverse()
    {
        if ($this->isZero()) {
            return $this;
        }

        $decimal = new Decimal();

        if ($this->isNegative()) {
            $decimal->value = substr($this->value, 1);
        } elseif ($this->isPositive()) {
            $decimal->value = '-' . $this->value;
        }

        $decimal->scale = $this->scale;

        return $decimal;
    }

    /**
     * "Rounds" the Decimal to have at most $scale digits after the point
     * @param  integer $scale
     * @return Decimal
     */
    public function round($scale = 0)
    {
        if ($scale >= $this->scale) {
            return $this;
        }

        return self::fromString(self::innerRound($this->value, $scale));
    }

    /**
     * Returns the absolute value (always a positive number)
     * @return Decimal
     */
    public function abs()
    {
        if ($this->isZero() || $this->isPositive()) {
            return $this;
        }

        return $this->additiveInverse();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->value;
    }

    /**
     * Validates basic constructor's arguments
     * @param  mixed    $value
     * @param  integer  $scale
     */
    private static function internalConstructorValidation($value, $scale)
    {
        if ($value === null) {
            throw new \InvalidArgumentException('$value must be a non null number');
        }

        if ($scale !== null && (!is_int($scale) || $scale < 0)) {
            throw new \InvalidArgumentException('$scale must be a positive integer');
        }
    }

    /**
     * Validates basic operator's arguments
     * @param  Decimal  $b      operand
     * @param  integer  $scale  bcmath scale param
     */
    private static function internalOperatorValidation(BigNumber $b, $scale)
    {
        if ($scale !== null && (!is_int($scale) || $scale < 0)) {
            throw new \InvalidArgumentException('$scale must be a positive integer');
        }
    }

    /**
     * "Rounds" the decimal string to have at most $scale digits after the point
     *
     * @param  string  $value
     * @param  integer $scale
     * @return string
     */
    private static function innerRound($value, $scale = 0)
    {
        $rounded = bcadd($value, '0', $scale);

        $diffDigit = bcsub($value, $rounded, $scale+1);
        $diffDigit = (int)$diffDigit[strlen($diffDigit)-1];

        if ($diffDigit >= 5) {
            $rounded = bcadd($rounded, bcpow('10', -$scale, $scale), $scale);
        }

        return $rounded;
    }

    /**
     * Calculates the logarithm (in base 10) of $value
     *
     * @param  string  $value     The number we want to calculate its logarithm (only positive numbers)
     * @param  integer $in_scale  Expected scale used by $value (only positive numbers)
     * @param  integer $out_scale Scale used by the return value (only positive numbers)
     * @return string
     */
    private static function innerLog10($value, $in_scale, $out_scale)
    {
        $value_len = strlen($value);

        $cmp = bccomp($value, '1', $in_scale);

        switch ($cmp) {
            case 1:
                $value_log10_approx = $value_len - ($in_scale > 0 ? ($in_scale+2) : 1);

                return bcadd(
                    $value_log10_approx,
                    log10(bcdiv(
                        $value,
                        bcpow('10', $value_log10_approx),
                        min($value_len, $out_scale)
                    )),
                    $out_scale
                );
            case -1:
                preg_match('/^0*\.(0*)[1-9][0-9]*$/', $value, $captures);
                $value_log10_approx = -strlen($captures[1])-1;

                return bcadd(
                    $value_log10_approx,
                    log10(bcmul(
                        $value,
                        bcpow('10', -$value_log10_approx),
                        $in_scale + $value_log10_approx
                    )),
                    $out_scale
                );
            default: // case 0:
                return '0';
        }
    }

    /**
     * Returns $base^$exponent
     *
     * @param  string $base
     * @param  string $exponent   0 < $exponent < 1
     * @param  integer $exp_scale Number of $exponent's significative digits
     * @param  integer $out_scale Number of significative digits that we want to compute
     * @return string
     */
    private static function innerPowWithLittleExponent($base, $exponent, $exp_scale, $out_scale)
    {
        $inner_scale = ceil($exp_scale*log(10)/log(2))+1;

        $result_a = '1';
        $result_b = '0';

        $actual_index = 0;
        $exponent_remaining = $exponent;

        while (bccomp($result_a, $result_b, $out_scale) !== 0 && bccomp($exponent_remaining, '0', $inner_scale) !== 0) {
            $result_b = $result_a;
            $index_info = self::computeSquareIndex($exponent_remaining, $actual_index, $exp_scale, $inner_scale);
            $exponent_remaining = $index_info[1];
            $result_a = bcmul(
                $result_a,
                self::compute2NRoot($base, $index_info[0], 2*($out_scale+1)),
                2*($out_scale+1)
            );
        }

        return self::innerRound($result_a, $out_scale);
    }

    /**
     * Auxiliar method. It helps us to decompose the exponent into many summands.
     *
     * @param  string  $exponent_remaining
     * @param  integer $actual_index
     * @param  integer $exp_scale           Number of $exponent's significative digits
     * @param  integer $inner_scale         ceil($exp_scale*log(10)/log(2))+1;
     * @return array
     */
    private static function computeSquareIndex($exponent_remaining, $actual_index, $exp_scale, $inner_scale)
    {
        $actual_rt = bcpow('0.5', $actual_index, $exp_scale);
        $r = bcsub($exponent_remaining, $actual_rt, $inner_scale);

        while (bccomp($r, 0, $exp_scale) === -1) {
            ++$actual_index;
            $actual_rt = bcmul('0.5', $actual_rt, $inner_scale);
            $r = bcsub($exponent_remaining, $actual_rt, $inner_scale);
        }

        return array($actual_index, $r);
    }

    /**
     * Auxiliar method. Computes $base^((1/2)^$index)
     *
     * @param  string  $base
     * @param  integer $index
     * @param  integer $out_scale
     * @return string
     */
    private static function compute2NRoot($base, $index, $out_scale)
    {
        $result = $base;

        for ($i=0; $i<$index; $i++) {
            $result = bcsqrt($result, ($out_scale+1)*($index-$i)+1);
        }

        return self::innerRound($result, $out_scale);
    }
}