<?php

use Litipk\BigNumbers\Decimal as Decimal;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('arccot')]
class DecimalArccotTest extends TestCase
{
    public static function arccotProvider() {
        // Some values provided by wolframalpha
        return [
            ['0.154', '1.41799671285823', 14],
            ['0', '1.57079632679489662', 17],
            ['-1', '-0.78540', 5],
        ];
    }

    #[DataProvider('arccotProvider')]
    public function testSimple($nr, $answer, $digits)
    {
        $x = Decimal::fromString($nr);
        $arccotX = $x->arccot($digits);

        $this->assertTrue(
            Decimal::fromString($answer)->equals($arccotX),
            "The answer must be " . $answer . ", but was " . $arccotX
        );
    }

}
