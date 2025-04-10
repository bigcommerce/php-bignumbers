<?php

use Litipk\BigNumbers\Decimal as Decimal;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('cosec')]
class DecimalCosecTest extends TestCase
{
    public static function cosecProvider() {
        // Some values provided by Mathematica
        return [
            ['1', '1.18839510577812', 14],
            ['123.123', '-1.76874094322450309', 17],
            ['15000000000', '1.44570405082842149818', 20]
        ];
    }

    #[DataProvider('cosecProvider')]
    public function testSimple($nr, $answer, $digits)
    {
        $x = Decimal::fromString($nr);
        $cosecX = $x->cosec((int)$digits);

        $this->assertTrue(
            Decimal::fromString($answer)->equals($cosecX),
            "The answer must be " . $answer . ", but was " . $cosecX
        );
    }
}
