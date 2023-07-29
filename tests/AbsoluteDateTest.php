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

    /**
     * @throws \Exception
     */
    public function testCreateRelative(): void
    {
        $date = AbsoluteDate::createRelative('yesterday', $timezone = new \DateTimeZone('America/Los_Angeles'));
        $now = new \DateTimeImmutable('yesterday', $timezone);
        self::assertSame($now->format(AbsoluteDate::DEFAULT_DATE_FORMAT), $date->format());
    }

    public function testSerialization(): void
    {
        $date = new AbsoluteDate('2022-01-01');
        $serialized = serialize($date);
        self::assertSame('O:32:"AssoConnect\PHPDate\AbsoluteDate":1:{s:4:"date";s:10:"2022-01-01";}', $serialized);
        self::assertSame('2022-01-01', unserialize($serialized)->format());
    }

    /** @dataProvider providerSerializedData */
    public function testUnserialization(string $serialized): void
    {
        self::assertSame('2022-01-01', unserialize($serialized)->format());
    }

    /** @return array<string>[] */
    public function providerSerializedData(): iterable
    {
        yield 'old format' => ['O:32:"AssoConnect\PHPDate\AbsoluteDate":1:{s:4:"date";s:18:"s:10:"2022-01-01";";}'];
        yield 'another old format' => [
            'O:32:"AssoConnect\PHPDate\AbsoluteDate":1:{s:42:" AssoConnect\PHPDate\AbsoluteDate datetime";O:17:"'
            . 'DateTimeImmutable":3:{s:4:"date";s:26:"2022-01-01 00:00:00.000000";s:13:"timezone_type";i:3;s:8:"'
            . 'timezone";s:3:"UTC";}}',
        ];
        yield 'new format' => ['O:32:"AssoConnect\PHPDate\AbsoluteDate":1:{s:4:"date";s:10:"2022-01-01";}'];
    }

    public function testComparison(): void
    {
        $dateBefore = new AbsoluteDate('2023-01-01');
        $dateAfter = new AbsoluteDate('2024-01-01');

        self::assertTrue($dateBefore->equals($dateBefore));
        self::assertFalse($dateBefore->equals($dateAfter));
        self::assertTrue($dateBefore->equalsTo($dateBefore));
        self::assertFalse($dateBefore->equalsTo($dateAfter));

        self::assertTrue($dateBefore->isBefore($dateAfter));
        self::assertFalse($dateBefore->isBefore($dateBefore));
        self::assertFalse($dateAfter->isBefore($dateBefore));

        self::assertTrue($dateBefore->isBeforeOrEqualTo($dateAfter));
        self::assertTrue($dateBefore->isBeforeOrEqualTo($dateBefore));
        self::assertFalse($dateAfter->isBeforeOrEqualTo($dateBefore));

        self::assertSame(-1, $dateBefore->compare($dateAfter));
        self::assertSame(0, $dateBefore->compare($dateBefore));
        self::assertSame(1, $dateAfter->compare($dateBefore));

        self::assertTrue($dateAfter->isAfter($dateBefore));
        self::assertFalse($dateAfter->isAfter($dateAfter));
        self::assertFalse($dateBefore->isAfter($dateAfter));

        self::assertTrue($dateAfter->isAfterOrEqualTo($dateBefore));
        self::assertTrue($dateAfter->isAfterOrEqualTo($dateAfter));
        self::assertFalse($dateBefore->isAfterOrEqualTo($dateAfter));

        self::assertTrue((new AbsoluteDate('2023-06-01'))->isBetween($dateBefore, $dateAfter));
        self::assertFalse((new AbsoluteDate('2020-01-01'))->isBetween($dateBefore, $dateAfter));
        self::assertFalse((new AbsoluteDate('2025-01-01'))->isBetween($dateBefore, $dateAfter));
        self::assertFalse($dateBefore->isBetween($dateBefore, $dateAfter));
        self::assertFalse($dateAfter->isBetween($dateBefore, $dateAfter));

        self::assertTrue((new AbsoluteDate('2023-06-01'))->isBetweenOrEqualTo($dateBefore, $dateAfter));
        self::assertFalse((new AbsoluteDate('2020-01-01'))->isBetweenOrEqualTo($dateBefore, $dateAfter));
        self::assertFalse((new AbsoluteDate('2025-01-01'))->isBetweenOrEqualTo($dateBefore, $dateAfter));
        self::assertTrue($dateBefore->isBetweenOrEqualTo($dateBefore, $dateAfter));
        self::assertTrue($dateAfter->isBetweenOrEqualTo($dateBefore, $dateAfter));
    }
}
