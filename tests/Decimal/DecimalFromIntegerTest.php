<?php
declare(strict_types=1);

use Litipk\BigNumbers\Decimal as Decimal;
use PHPUnit\Framework\TestCase;

\date_default_timezone_set('UTC');

class DecimalFromIntegerTest extends TestCase
{
    public function testNoInteger()
    {
        $this->expectException(\TypeError::class);
        Decimal::fromInteger(5.1);
    }
}
