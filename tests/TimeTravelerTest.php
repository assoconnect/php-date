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
        yield ['2020-01-01', '2020-01-01', '2020-02-01'];
        yield ['2020-01-01', '2020-02-01', '2020-03-01'];

        yield ['2020-01-25', '2020-02-25', '2020-03-25'];

        // Test the result day matches the reference day
        yield ['2020-01-30', '2020-02-29', '2020-03-30'];
        yield ['2020-01-31', '2020-02-29', '2020-03-31'];
        yield ['2020-01-31', '2020-03-31', '2020-04-30'];
        yield ['2020-06-30', '2020-06-30', '2020-07-31'];
        yield ['2020-06-30', '2020-07-31', '2020-08-31'];
        yield ['2020-06-30', '2020-08-31', '2020-09-30'];

        // $from drifted away from the reference day: the result is the occurrence of that day nearest to one
        // month after $from
        yield ['2020-01-31', '2020-08-01', '2020-08-31'];
        yield ['2020-01-31', '2020-08-02', '2020-08-31'];
        yield ['2020-01-31', '2020-08-14', '2020-08-31'];
        yield ['2020-01-31', '2020-08-16', '2020-09-30'];
        yield ['2020-01-29', '2020-05-01', '2020-05-29'];
        yield ['2020-01-15', '2020-08-20', '2020-09-15'];
        yield ['2020-01-15', '2020-01-10', '2020-02-15'];
        yield ['2020-01-15', '2020-01-20', '2020-02-15'];
        yield ['2020-01-31', '2020-07-30', '2020-08-31'];
        yield ['2020-01-01', '2020-01-31', '2020-03-01'];
        yield ['2020-01-15', '2020-01-31', '2020-02-15'];
        // two occurrences equally near: the later one
        yield ['2020-01-31', '2020-08-15', '2020-09-30'];
        yield ['2025-01-31', '2025-05-15', '2025-06-30'];
        // the reference day is clamped to a shorter month
        yield ['2020-01-31', '2020-02-10', '2020-02-29'];
        yield ['2021-01-31', '2021-02-10', '2021-02-28'];
        // a reference on the last day of its month means the last day of every month
        yield ['2020-04-30', '2020-08-10', '2020-08-31'];
        yield ['2021-02-28', '2021-03-10', '2021-03-31'];
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
