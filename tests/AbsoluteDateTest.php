<?php

declare(strict_types=1);

namespace AssoConnect\PHPDate\Tests;

use AssoConnect\PHPDate\AbsoluteDate;
use AssoConnect\PHPDate\Exception\ParsingException;
use PHPUnit\Framework\TestCase;
use Symfony\Bridge\PhpUnit\ClockMock;

class AbsoluteDateTest extends TestCase
{
    public function testParsingException(): void
    {
        $this->expectException(ParsingException::class);
        new AbsoluteDate('abc', 'def');
    }

    public function testResetsNonDatePartsToZeroUnixTimeValues(): void
    {
        $date = new AbsoluteDate('2020-01-01');
        $this->assertSame('00:00:00', $date->format('H:i:s'));
    }

    public function testToString(): void
    {
        $date = new AbsoluteDate('2020-01-01');
        $this->assertSame('2020-01-01', (string) $date);
    }

    public function testFormat(): void
    {
        $date = new AbsoluteDate('2020-01-02');
        $this->assertSame('2020-01-02', $date->format());
        $this->assertSame('2020-02-01', $date->format('Y-d-m'));
    }

    public function testWithPointInTime(): void
    {
        $date = new AbsoluteDate('2020-01-02');
        $this->assertSame('2020-01-02', $date->format());

        $datetime = \DateTime::createFromFormat(
            'Y-m-d H:i:s',
            '2019-12-27 23:00:00',
            new \DateTimeZone('UTC')
        );

        $date = AbsoluteDate::createInTimezone(new \DateTimeZone('UTC'), $datetime);
        $this->assertSame('2019-12-27', $date->format());

        $date = AbsoluteDate::createInTimezone(new \DateTimeZone('Europe/Paris'), $datetime);
        $this->assertSame('2019-12-28', $date->format());

        $date = AbsoluteDate::createInTimezone(new \DateTimeZone('America/Los_Angeles'), $datetime);
        $this->assertSame('2019-12-27', $date->format());
    }
}
