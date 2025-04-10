<?php

use Litipk\BigNumbers\Decimal as Decimal;
use Litipk\BigNumbers\DecimalConstants as DecimalConstants;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('tan')]
class DecimalTanTest extends TestCase
{
    public static function tanProvider() {
        // Some values providede by mathematica
        return [
            ['1', '1.55740772465490', 14],
            ['123.123', '0.68543903342472368', 17],
            ['15000000000', '-0.95779983511717825557', 20]
        ];
    }

    #[DataProvider('tanProvider')]
    public function testSimple($nr, $answer, $digits)
    {
        $x = Decimal::fromString($nr);
        $tanX = $x->tan($digits);
        $this->assertTrue(
            Decimal::fromString($answer)->equals($tanX),
            'tan('.$nr.') must be equal to '.$answer.', but was '.$tanX
        );
    }

    public function testTanPiTwoDiv()
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage("The tangent of this 'angle' is undefined.");
        $PiDividedByTwo = DecimalConstants::PI()->div(Decimal::fromInteger(2));
        $PiDividedByTwo->tan();
    }

}
