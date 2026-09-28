<?php

declare(strict_types=1);

namespace AssoConnect\PHPDate\Tests;

use AssoConnect\PHPDate\AbsoluteDate;
use AssoConnect\PHPDate\TimeTraveler;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class TimeTravelerTest extends TestCase
{
    private TimeTraveler $timeTraveler;

    public function setUp(): void
    {
        $this->timeTraveler = new TimeTraveler();
    }

    #[DataProvider('provideAddMonths')]
    public function testAddMonth(string $from, string $expected): void
    {
        self::assertSame($expected, $this->timeTraveler->addMonth(new AbsoluteDate($from))->__toString());
    }

    /** @return array{string, string}[] */
    public static function provideAddMonths(): iterable
    {
        yield ['2020-01-01', '2020-02-01'];
        yield ['2020-01-28', '2020-02-28'];
        yield ['2020-01-29', '2020-02-29'];
        yield ['2020-01-30', '2020-02-29'];
        yield ['2020-01-31', '2020-02-29'];
        yield ['2020-02-29', '2020-03-31'];
        yield ['2020-06-30', '2020-07-31'];
    }

    #[DataProvider('provideRemoveMonths')]
    public function testRemoveMonth(string $from, string $expected): void
    {
        self::assertSame($expected, $this->timeTraveler->removeMonth(new AbsoluteDate($from))->__toString());
    }

    /** @return iterable<string, array{string, string}> */
    public static function provideRemoveMonths(): iterable
    {
        foreach (
            [
                '2020-01-01' => '2019-12-01',
                '2020-01-28' => '2019-12-28',
                '2020-01-29' => '2019-12-29',
                '2020-01-30' => '2019-12-30',
                '2020-01-31' => '2019-12-31',
                '2020-02-29' => '2020-01-31',
                '2020-06-30' => '2020-05-31',
                '2023-10-31' => '2023-09-30',
                '2023-08-31' => '2023-07-31',
                '2023-09-30' => '2023-08-31',
                '2023-03-31' => '2023-02-28',
                '2024-03-31' => '2024-02-29',
            ] as $currentMonth => $expectedPreviousMonth
        ) {
            yield sprintf('%s: previous month will %s', $currentMonth, $expectedPreviousMonth) => [
                $currentMonth,
                $expectedPreviousMonth,
            ];
        }
    }

    #[DataProvider('provideMonthsWithReference')]
    public function testAddMonthWithReference(string $reference, string $from, string $expected): void
    {
        self::assertSame($expected, $this->timeTraveler->addMonthWithReference(
            new AbsoluteDate($reference),
            new AbsoluteDate($from)
        )->__toString());
    }

    /** @return array{string, string, string}[] */
    public static function provideMonthsWithReference(): iterable
    {
        // $from on the reference day: the reference day of the following month
        yield ['2020-01-01', '2020-01-01', '2020-02-01'];
        yield ['2020-01-01', '2020-02-01', '2020-03-01'];
        yield ['2020-01-25', '2020-02-25', '2020-03-25'];
        yield ['2020-01-15', '2020-12-15', '2021-01-15'];
        yield ['2020-02-29', '2020-02-29', '2020-03-31'];
        yield ['2020-01-31', '2020-01-31', '2020-02-29'];

        // $from on a clamped last day of a shorter month: back on the reference day
        yield ['2020-01-30', '2020-02-29', '2020-03-30'];
        yield ['2020-01-31', '2020-02-29', '2020-03-31'];
        yield ['2021-01-31', '2021-02-28', '2021-03-31'];
        yield ['2021-01-29', '2021-02-28', '2021-03-29'];
        yield ['2020-01-31', '2020-03-31', '2020-04-30'];
        yield ['2020-01-31', '2020-04-30', '2020-05-31'];
        yield ['2020-01-31', '2020-11-30', '2020-12-31'];
        yield ['2020-01-31', '2020-12-31', '2021-01-31'];

        // a reference on the last day of its month means the last day of every month
        yield ['2020-06-30', '2020-06-30', '2020-07-31'];
        yield ['2020-06-30', '2020-07-31', '2020-08-31'];
        yield ['2020-06-30', '2020-08-31', '2020-09-30'];
        yield ['2021-02-28', '2021-02-28', '2021-03-31'];
        yield ['2021-02-28', '2021-03-31', '2021-04-30'];
        yield ['2020-04-30', '2020-08-10', '2020-08-31'];
        yield ['2021-02-28', '2021-03-10', '2021-03-31'];
        yield ['2020-06-30', '2020-07-01', '2020-07-31'];
        yield ['2020-06-30', '2020-07-16', '2020-07-31'];

        // $from before the reference day of its month: the reference day of that same month, since the following
        // month's would be more than a month away
        yield ['2020-01-31', '2020-08-01', '2020-08-31'];
        yield ['2020-01-31', '2020-08-02', '2020-08-31'];
        yield ['2020-01-31', '2020-08-15', '2020-08-31'];
        yield ['2020-01-31', '2020-08-16', '2020-08-31'];
        yield ['2020-01-29', '2020-05-01', '2020-05-29'];
        yield ['2020-01-15', '2020-01-01', '2020-01-15'];
        yield ['2020-01-15', '2020-01-10', '2020-01-15'];
        yield ['2020-01-15', '2020-01-14', '2020-01-15'];
        yield ['2025-01-31', '2025-05-15', '2025-05-31'];
        yield ['2021-01-31', '2021-01-01', '2021-01-31'];

        // $from the day before the reference day: a single-day step
        yield ['2020-01-31', '2020-07-30', '2020-07-31'];
        yield ['2020-01-15', '2020-03-14', '2020-03-15'];
        yield ['2020-01-02', '2020-03-01', '2020-03-02'];
        yield ['2020-01-01', '2020-01-31', '2020-02-01'];
        yield ['2020-01-01', '2020-02-29', '2020-03-01'];
        yield ['2020-01-01', '2020-04-30', '2020-05-01'];

        // $from after the reference day of its month: the reference day of the following month
        yield ['2020-01-15', '2020-01-20', '2020-02-15'];
        yield ['2020-01-15', '2020-08-20', '2020-09-15'];
        yield ['2020-01-15', '2020-01-31', '2020-02-15'];
        yield ['2020-01-01', '2020-01-02', '2020-02-01'];
        yield ['2020-01-01', '2020-01-16', '2020-02-01'];
        yield ['2020-01-10', '2020-12-20', '2021-01-10'];

        // the reference day does not exist in the month reached: its last day is the anchor, and being one month
        // after $from at most, it is preferred to the reference day of the month of $from
        yield ['2020-01-31', '2020-02-10', '2020-02-29'];
        yield ['2021-01-31', '2021-02-10', '2021-02-28'];
        yield ['2020-01-31', '2020-04-10', '2020-04-30'];
        yield ['2020-01-31', '2020-01-30', '2020-02-29'];
        yield ['2020-01-30', '2020-01-29', '2020-02-29'];
        yield ['2021-01-30', '2021-01-29', '2021-02-28'];
        yield ['2020-01-29', '2021-01-30', '2021-02-28'];
        yield ['2020-01-31', '2020-08-30', '2020-09-30'];

        // across a year boundary
        yield ['2020-01-31', '2020-12-31', '2021-01-31'];
        yield ['2020-01-31', '2020-12-15', '2020-12-31'];
        yield ['2020-01-15', '2020-12-20', '2021-01-15'];
        yield ['2020-01-01', '2020-12-31', '2021-01-01'];
        yield ['2020-06-30', '2020-12-10', '2020-12-31'];
    }

    /**
     * A date that drifted away from the reference day rejoins it at the very next step, and every later step
     * lands on the reference day of the following month.
     */
    public function testAddMonthWithReferenceRejoinsTheReferenceDayAfterADrift(): void
    {
        $reference = new AbsoluteDate('2020-01-31');
        $actual = new AbsoluteDate('2020-08-02');

        foreach (['2020-08-31', '2020-09-30', '2020-10-31', '2020-11-30', '2020-12-31', '2021-01-31'] as $expected) {
            $actual = $this->timeTraveler->addMonthWithReference($reference, $actual);

            self::assertSame($expected, $actual->__toString());
        }
    }

    /**
     * Whatever the reference day and the starting date (every day of 2020 and 2021, so leap and non-leap
     * Februaries and two year boundaries), the result is after the start, never past one month later, and on the
     * reference day of its month — that month's last day when the reference day does not exist in it or when the
     * reference is itself a last day of month.
     */
    public function testAddMonthWithReferenceAlwaysLandsOnTheReferenceDayWithinOneMonth(): void
    {
        $references = [];
        for ($referenceDay = 1; $referenceDay <= 31; $referenceDay++) {
            $references[] = new AbsoluteDate(sprintf('2020-01-%02d', $referenceDay));
        }
        $references[] = new AbsoluteDate('2021-02-28');
        $references[] = new AbsoluteDate('2020-02-29');
        $references[] = new AbsoluteDate('2020-04-30');

        $from = new AbsoluteDate('2019-12-01');
        while ($from->isBefore(new AbsoluteDate('2022-02-01'))) {
            $oneMonthLater = $this->timeTraveler->addMonth($from);
            foreach ($references as $reference) {
                $actual = $this->timeTraveler->addMonthWithReference($reference, $from);
                $context = sprintf('reference %s, from %s, got %s', $reference, $from, $actual);

                self::assertTrue($from->isBefore($actual), $context);
                self::assertTrue($actual->isBeforeOrEqualTo($oneMonthLater), $context . ', past ' . $oneMonthLater);
                self::assertSame($this->expectedDayIn($actual, $reference), $actual->format('j'), $context);
            }
            $from = $from->modify('+1 day');
        }
    }

    /** The day the reference lands on in the month of $date, as a string like AbsoluteDate::format('j') gives it */
    private function expectedDayIn(AbsoluteDate $date, AbsoluteDate $reference): string
    {
        $lastDayOfMonth = $date->modify('last day of this month')->format('j');
        if ($reference->equalsTo($reference->modify('last day of this month'))) {
            return $lastDayOfMonth;
        }

        return (string) min((int) $reference->format('j'), (int) $lastDayOfMonth);
    }

    #[DataProvider('provideMonthsWithReferenceOverYear')]
    public function testAddMonthWithReferenceWorksYearOverYear(string $referenceAsString, string $expected): void
    {
        $reference = new AbsoluteDate($referenceAsString);

        for ($i = 0; $i < 12; $i++) {
            $actual = $this->timeTraveler->addMonthWithReference($reference, $actual ?? $reference);
        }
        self::assertTrue(isset($actual));
        self::assertSame($expected, $actual->__toString());
    }

    /** @return array{string, string}[] */
    public static function provideMonthsWithReferenceOverYear(): iterable
    {
        yield ['2020-01-01', '2021-01-01'];
        yield ['2020-01-15', '2021-01-15'];
        yield ['2020-01-28', '2021-01-28'];
        yield ['2020-01-29', '2021-01-29'];
        yield ['2020-01-30', '2021-01-30'];
        yield ['2020-01-31', '2021-01-31'];
        yield ['2020-06-30', '2021-06-30'];
    }

    #[DataProvider('provideYears')]
    public function testAddYear(string $from, string $expected): void
    {
        self::assertSame($expected, $this->timeTraveler->addYear(new AbsoluteDate($from))->__toString());
    }

    /** @return array{string, string}[] */
    public static function provideYears(): iterable
    {
        yield ['2020-01-01', '2021-01-01'];
        yield ['2020-01-31', '2021-01-31'];
        yield ['2020-02-29', '2021-02-28'];
        yield ['2020-06-30', '2021-06-30'];
    }
}
