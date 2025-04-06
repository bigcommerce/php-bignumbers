<?php

use Litipk\BigNumbers\Decimal as Decimal;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('cos')]
class DecimalCosTest extends TestCase
{
    public static function cosProvider() {
        // Some values provided by Mathematica
        return [
            ['1', '0.54030230586814', 14],
            ['123.123', '-0.82483472946164834', 17],
            ['15000000000', '-0.72218064388924347683', 20]
        ];
    }

    #[DataProvider('cosProvider')]
    public function testSimple($nr, $answer, $digits)
    {
        $x = Decimal::fromString($nr);
        $cosX = $x->cos((int)$digits);

        $this->assertTrue(
            Decimal::fromString($answer)->equals($cosX),
            "The answer must be " . $answer . ", but was " . $cosX
        );
    }
}
