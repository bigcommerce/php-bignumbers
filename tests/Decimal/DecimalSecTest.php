<?php

use Litipk\BigNumbers\Decimal as Decimal;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('sec')]
class DecimalSecTest extends TestCase
{
    public static function SecProvider() {
        // Some values provided by Mathematica
        return [
            ['5', '3.52532008581609', 14],
            ['456.456', '-1.66172995090378344', 17],
            ['28000000000', '-1.11551381955633891873', 20],
        ];
    }

    #[DataProvider('secProvider')]
    public function testSimple($nr, $answer, $digits)
    {
        $x = Decimal::fromString($nr);
        $secX = $x->sec((int)$digits);

        $this->assertTrue(
            Decimal::fromString($answer)->equals($secX),
            "The answer must be " . $answer . ", but was " . $secX
        );
    }
}
