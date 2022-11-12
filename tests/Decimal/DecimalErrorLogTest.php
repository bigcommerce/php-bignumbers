<?php
declare(strict_types=1);

use Litipk\BigNumbers\Decimal;
use Litipk\BigNumbers\Errors\NaNInputError;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;

class DecimalErrorLogTest extends TestCase
{
    public function testLoggingUnhandledExceptionFromStaticMethod(): void
    {
        $logger = $this->prophesize(LoggerInterface::class);
        $logger->error(
            Argument::containingString('Decimal::fromString() failed with exception'),
            Argument::withKey('exception')
        )->shouldBeCalled();

        Decimal::setLogger($logger->reveal());

        $this->expectException(NaNInputError::class);
        Decimal::fromString('5.non');
    }
}