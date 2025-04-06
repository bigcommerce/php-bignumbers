<?php

use Litipk\BigNumbers\Decimal as Decimal;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('mod')]
class DecimalModTest extends TestCase
{
    public static function modProvider() {
        return [
            ['10', '3', '1'],
            ['34', '3.4', '0'],
            ['15.1615', '3.156156', '2.536876'],
            ['15.1615', '3.156156', '2.5369', 4],
            ['-3.4', '-2', '-1.4'],
            ['3.4', '-2', '-0.6'],
            ['-3.4', '2', '0.6']
        ];
    }

    #[DataProvider('modProvider')]
    public function testFiniteFiniteMod($number, $mod, $answer, $scale = null) {
        $numberDec = Decimal::fromString($number);
        $modDec = Decimal::fromString($mod);
        $decimalAnswer = $numberDec->mod($modDec, $scale);

        $this->assertTrue(
            Decimal::fromString($answer)->equals($decimalAnswer),
            $decimalAnswer . ' % ' . $mod . ' must be equal to ' . $answer . ', but was ' . $decimalAnswer
        );
    }
}
