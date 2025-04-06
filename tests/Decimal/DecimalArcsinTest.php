<?php

use Litipk\BigNumbers\Decimal as Decimal;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('arcsin')]
class DecimalArcsinTest extends TestCase
{
    public static function arcsinProvider() {
        // Some values provided by wolframalpha
        return [
            ['0.154', '0.15461530016096', 14],
            ['1', '1.57079632679489662', 17],
            ['-1', '-1.57079632679489662', 17],
        ];
    }

    #[DataProvider('arcsinProvider')]
    public function testSimple($nr, $answer, $digits)
    {
        $x = Decimal::fromString($nr);
        $arcsinX = $x->arcsin($digits);

        $this->assertTrue(
            Decimal::fromString($answer)->equals($arcsinX),
            "The answer must be " . $answer . ", but was " . $arcsinX
        );
    }

    public function testArcsinGreaterThanOne()
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage("The arcsin of this number is undefined.");
        Decimal::fromString('25.546')->arcsin();
    }

    public function testArcsinFewerThanNegativeOne()
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage("The arcsin of this number is undefined.");
        Decimal::fromString('-304.75')->arcsin();
    }
}
