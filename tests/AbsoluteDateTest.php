<?php

declare(strict_types=1);

namespace AssoConnect\PHPDate\Tests;

use AssoConnect\PHPDate\AbsoluteDate;
use AssoConnect\PHPDate\Exception\ParsingException;
use PHPUnit\Framework\TestCase;
use Symfony\Bridge\PhpUnit\ClockMock;

class AbsoluteDateTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        date_default_timezone_set('UTC');
        ClockMock::register(AbsoluteDate::class);
    }

    public function tearDown(): void
    {
        ClockMock::withClockMock(false);
    }

    public function testParsingException(): void
    {
        $this->expectException(ParsingException::class);
        new AbsoluteDate('abc', 'def');
    }

    public function testResetsNonDatePartsToZeroUnixTimeValues(): void
    {
        // Using now
        $date = new AbsoluteDate();
        $this->assertSame('00:00:00', $date->format('H:i:s'));

        // Using a given date
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

    public function testNow(): void
    {
        ClockMock::withClockMock(0);

        $date = new AbsoluteDate('now');
        $this->assertSame('1970-01-01', $date->format('Y-m-d'));

        ClockMock::withClockMock(mktime(23, 59, 59, 12, 27, 2019));

        $date = new AbsoluteDate('now');
        $this->assertSame('2019-12-27', $date->format());

        $date = AbsoluteDate::createInTimezone(new \DateTimeZone('UTC'));
        $this->assertSame('2019-12-27', $date->format());

        $date = AbsoluteDate::createInTimezone(new \DateTimeZone('Europe/Paris'));
        $this->assertSame('2019-12-28', $date->format());

        $date = AbsoluteDate::createInTimezone(new \DateTimeZone('America/Los_Angeles'));
        $this->assertSame('2019-12-27', $date->format());
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

    public function testFromTimestamp(): void
    {
        $date = new \DateTimeImmutable('2020-02-28');
        $date = AbsoluteDate::fromTimestamp($date->getTimestamp());

        $this->assertSame('2020-02-28 00:00:00', $date->format('Y-m-d H:i:s'));
    }

    public function testToTimestamp(): void
    {
        $date = new AbsoluteDate('2020-02-28');

        $this->assertSame($date->toTimestamp(), mktime(0, 0, 0));
    }

    public function testToDateTimeImmutable(): void
    {
        $date = new AbsoluteDate();

        $dateTime = $date->toDateTimeImmutable();

        $this->assertEquals('00:00:00', $dateTime->format('H:i:s'));
    }

    public function testToDateTime(): void
    {
        $date = new AbsoluteDate();

        $dateTime = $date->toDateTime();

        $this->assertEquals('00:00:00', $dateTime->format('H:i:s'));
    }

    public function testModifyTimeShouldNotChangeTime(): void
    {
        $date = new AbsoluteDate();

        $modifiedDate = $date->modify('+10 minutes');

        $this->assertEquals($date, $modifiedDate);
    }

    public function testModifyDate(): void
    {
        $date = new AbsoluteDate('2020-02-28');

        $modifiedDate = $date->modify('+3 days');

        $this->assertEquals('2020-03-02', $modifiedDate->format());
    }
}
