<?php
declare(strict_types=1);

use Litipk\BigNumbers\Decimal as Decimal;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class DecimalFromFloatTest extends TestCase
{
    public function testNaN()
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage("fltValue can't be NaN");
        Decimal::fromFloat(INF - INF);
    }

    public static function floatProvider()
    {
        $tests = [
            [1.1, "1.1"],
            [1234567890.0, "1234567890"],
            [1.1234567890, "1.123456789"],
            [-1.1234567890, "-1.123456789"],
            [0.000001, "0.0000010"],
            [0.000001, "0.00", 2],
            [90.05, "90.05"],
        ];

        if (PHP_INT_SIZE >= 8) {
            // These tests probably won't work if you're not testing on x86-64.
            // It might also be better to mark the tests skipped. It is certainly
            // useful to cover this functionality off though as it hits the exponent
            // parsing in Decimal::fromFloat()
            $tests[] = [
                 1230123074129038740129734907810923874017283094.1, 
                "1230123074129038665578332283019326242900934656.0000000000000000"
            ];
            $tests[] = [
                 1230123074129038740129734907810923874017283094.1, 
                "1230123074129038665578332283019326242900934656",
                0
            ];
            $tests[] = [
                 0.0000000000000000000000000000000000000000000000123412351234,
                "0.0000000000000000000000000000000000000000000000123412351234",
            ];
            $tests[] = [
                 0.0000000000000000000000000000000000000000000000123412351234,
                "0.00",
                2
            ];
        }
        return $tests;
    }

    #[DataProvider('floatProvider')]
    public function testFromFloat(float $in, string $str, ?int $scale = null)
    {
        $v = Decimal::fromFloat($in, $scale);
        $this->assertSame($str, $v->innerValue());
    }
}
