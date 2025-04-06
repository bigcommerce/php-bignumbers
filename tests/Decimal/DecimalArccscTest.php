<?php

use Litipk\BigNumbers\Decimal as Decimal;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('arccsc')]
class DecimalArccscTest extends TestCase
{
    public static function arccscProvider() {
        // Some values provided by wolframalpha
        return [
            ['25.546', '0.03915507577327', 14],
            ['1.5', '0.729728', 6],
            ['1', '1.57079632679489662', 17],
            ['-1', '-1.57079632679489662', 17],
        ];
    }

    #[DataProvider('arccscProvider')]
    public function testSimple($nr, $answer, $digits)
    {
        $x = Decimal::fromString($nr);
        $arccscX = $x->arccsc($digits);

        $this->assertTrue(
            Decimal::fromString($answer)->equals($arccscX),
            "The answer must be " . $answer . ", but was " . $arccscX
        );
    }

    public function testArccscBetweenOneAndNegativeOne()
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage("The arccosecant of this number is undefined.");
        Decimal::fromString('0.546')->arccsc();
    }
}
