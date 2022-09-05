<?php

declare(strict_types=1);

namespace AssoConnect\PHPDate\Tests;

use AssoConnect\PHPDate\AbsoluteDate;
use AssoConnect\PHPDate\Exception\ParsingException;
use PHPUnit\Framework\TestCase;

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
        self::assertSame('00:00:00', $date->format('H:i:s'));
    }

    public function testToString(): void
    {
        $date = new AbsoluteDate('2020-01-01');
        self::assertSame('2020-01-01', (string) $date);
    }

    public function testFormat(): void
    {
        $date = new AbsoluteDate('2020-01-02');
        self::assertSame('2020-01-02', $date->format());
        self::assertSame('2020-02-01', $date->format('Y-d-m'));
    }

    public function testModifyResultIsCorrect(): void
    {
        $date = new AbsoluteDate('2020-01-02');
        $newDate = $date->modify('+2 days');
        self::assertNotSame($date, $newDate);
        self::assertSame('2020-01-02', $date->format());
        self::assertSame('2020-01-04', $newDate->format());

        $newDate = $date->modify('+1 month');
        self::assertSame('2020-02-02', $newDate->format());
    }

    /** @dataProvider providerModifyEnforcePattern */
    public function testModifyEnforcePattern(string $pattern, bool $patternIsValid): void
    {
        if ($patternIsValid) {
            self::expectNotToPerformAssertions();
        } else {
            $this->expectException(\DomainException::class);
        }

        $date = new AbsoluteDate('2020-01-01');
        $date->modify($pattern);
    }

    /**
     * @return iterable<mixed>
     */
    public function providerModifyEnforcePattern(): iterable
    {
        // Invalid pattern
        yield ['+1 second', false];

        // Valid pattern
        yield ['+1 day', true];
        yield ['1 day ago', true];
        yield ['last day of this month', true];
    }

    public function testWithPointInTime(): void
    {
        $date = new AbsoluteDate('2020-01-02');
        self::assertSame('2020-01-02', $date->format());

        $datetime = \DateTime::createFromFormat(
            'Y-m-d H:i:s',
            '2019-12-27 23:00:00',
            new \DateTimeZone('UTC')
        );
        self::assertNotFalse($datetime);

        $date = AbsoluteDate::createInTimezone(new \DateTimeZone('UTC'), $datetime);
        self::assertSame('2019-12-27', $date->format());

        $date = AbsoluteDate::createInTimezone(new \DateTimeZone('Europe/Paris'), $datetime);
        self::assertSame('2019-12-28', $date->format());

        $date = AbsoluteDate::createInTimezone(new \DateTimeZone('America/Los_Angeles'), $datetime);
        self::assertSame('2019-12-27', $date->format());
    }

    public function testStartsAt(): void
    {
        $date1 = \DateTime::createFromFormat(
            'Y-m-d H:i:s',
            '2019-12-27 00:00:00',
            new \DateTimeZone('Europe/Paris')
        );

        $date2 = \DateTime::createFromFormat(
            'Y-m-d H:i:s',
            '2019-12-27 00:00:00',
            new \DateTimeZone('America/Los_Angeles')
        );

        $date = new AbsoluteDate('2019-12-27');

        self::assertEquals($date1, $date->startsAt(new \DateTimeZone('Europe/Paris')));
        self::assertEquals($date2, $date->startsAt(new \DateTimeZone('America/Los_Angeles')));
    }

    public function testEndsAt(): void
    {
        $date1 = \DateTime::createFromFormat(
            'Y-m-d H:i:s',
            '2019-12-27 23:59:59',
            new \DateTimeZone('Europe/Paris')
        );

        $date2 = \DateTime::createFromFormat(
            'Y-m-d H:i:s',
            '2019-12-27 23:59:59',
            new \DateTimeZone('America/Los_Angeles')
        );

        $date = new AbsoluteDate('2019-12-27');

        self::assertEquals($date1, $date->endsAt(new \DateTimeZone('Europe/Paris')));
        self::assertEquals($date2, $date->endsAt(new \DateTimeZone('America/Los_Angeles')));
    }

    public function testEquals(): void
    {
        $date = new AbsoluteDate('2022-01-01');

        self::assertTrue($date->equals(new AbsoluteDate('2022-01-01')));
        self::assertFalse($date->equals(new AbsoluteDate('2022-01-02')));
    }
    
    
    public function testSerialization(): void
    {
        $date = new AbsoluteDate('2022-01-01');
        self::assertSame('2022-01-01', unserialize(serialize($date))->format());
    }
}
