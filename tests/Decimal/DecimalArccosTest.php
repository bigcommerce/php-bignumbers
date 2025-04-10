<?php

use Litipk\BigNumbers\Decimal as Decimal;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('arccos')]
class DecimalArccosTest extends TestCase
{
    public static function arccosProvider() {
        // Some values provided by wolframalpha
        return [
            ['0.154', '1.41618102663394', 14],
            ['1', '0', 17],
            ['-1', '3.14159265358979324', 17],
        ];
    }

    #[DataProvider('arccosProvider')]
    public function testSimple($nr, $answer, $digits)
    {
        $x = Decimal::fromString($nr);
        $arccosX = $x->arccos($digits);

        $this->assertTrue(
            Decimal::fromString($answer)->equals($arccosX),
            "The answer must be " . $answer . ", but was " . $arccosX
        );
    }

    public function testArcosGreaterThanOne()
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage("The arccos of this number is undefined.");
        Decimal::fromString('25.546')->arccos();
    }

    public function testArccosFewerThanNegativeOne()
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage("The arccos of this number is undefined.");
        Decimal::fromString('-304.75')->arccos();
    }
}
