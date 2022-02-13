<?php

declare(strict_types=1);

namespace AssoConnect\PHPDate\Tests;

use AssoConnect\PHPDate\AbsoluteDate;
use AssoConnect\PHPDate\AbsoluteDatePeriod;
use PHPUnit\Framework\TestCase;

class AbsoluteDatePeriodTest extends TestCase
{
    public function testConstructorWorks(): void
    {
        $sut = new AbsoluteDatePeriod(
            $start = new AbsoluteDate('2021-01-01'),
            $end = new AbsoluteDate('2021-02-28')
        );

        self::assertSame($start, $sut->getStart());
        self::assertSame($end, $sut->getEnd());
    }
}
