<?php

use Litipk\BigNumbers\Decimal as Decimal;
use PHPUnit\Framework\TestCase;

/**
 * @group arcsec
 */
class DecimalArcsecTest extends TestCase
{
    public function arcsecProvider() {
        // Some values provided by wolframalpha
        return [
            ['25.546', '1.53164125102163', 14],
            ['1.5', '0.841068', 6],
            ['1', '0', 17],
            ['-1', '3.14159265358979324', 17],
        ];
    }

    /**
     * @dataProvider arcsecProvider
     */
    public function testSimple($nr, $answer, $digits)
    {
        $x = Decimal::fromString($nr);
        $arcsecX = $x->arcsec($digits);

        $this->assertTrue(
            Decimal::fromString($answer)->equals($arcsecX),
            "The answer must be " . $answer . ", but was " . $arcsecX
        );
    }

    public function testArcsecBetweenOneAndNegativeOne()
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage("The arcsecant of this number is undefined.");
        Decimal::fromString('0.546')->arcsec();
    }
}
