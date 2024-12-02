<?php

use Litipk\BigNumbers\Decimal as Decimal;
use PHPUnit\Framework\TestCase;

date_default_timezone_set('UTC');

class DecimalInternalValidationTest extends TestCase
{
    public function testConstructorNullValueValidation()
    {
        $this->expectException(\TypeError::class);
        Decimal::fromInteger(null);
    }

    public function testConstructorNegativeScaleValidation()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('$scale must be a positive integer');
        Decimal::fromString("25", -15);
    }

    public function testOperatorNegativeScaleValidation()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('$scale must be a positive integer');
        $one = Decimal::fromInteger(1);

        $one->mul($one, -1);
    }
}
