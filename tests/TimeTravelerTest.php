<?php

declare(strict_types=1);

namespace AssoConnect\PHPDate\Tests;

use AssoConnect\PHPDate\AbsoluteDate;
use AssoConnect\PHPDate\TimeTraveler;
use PHPUnit\Framework\TestCase;

class TimeTravelerTest extends TestCase
{
    private TimeTraveler $timeTraveler;

    public function setUp(): void
    {
        $this->timeTraveler = new TimeTraveler();
    }

    /** @dataProvider provideMonths */
    public function testAddMonth(string $from, string $expected): void
    {
        self::assertSame($expected, $this->timeTraveler->addMonth(new AbsoluteDate($from))->__toString());
    }

    /** @return array{string, string}[] */
    public function provideMonths(): iterable
    {
        yield ['2020-01-01', '2020-02-01'];
        yield ['2020-01-28', '2020-02-28'];
        yield ['2020-01-29', '2020-02-29'];
        yield ['2020-01-30', '2020-02-29'];
        yield ['2020-01-31', '2020-02-29'];
        yield ['2020-02-29', '2020-03-31'];
        yield ['2020-06-30', '2020-07-31'];
        yield ['2023-08-28', '2023-09-28'];
        yield ['2023-07-02', '2023-08-02'];
        yield ['2023-06-30', '2023-07-30'];
        yield ['2023-04-30', '2023-05-30'];
        yield ['2023-02-28', '2023-03-28'];
        yield ['2023-01-06', '2023-02-06'];
        yield ['2023-01-03', '2023-02-03'];
        yield ['2023-01-02', '2023-02-02'];
        yield ['2023-01-01', '2023-02-01'];
    }

    /** @dataProvider provideRemoveMonths */
    public function testRemoveMonth(string $from, string $expected): void
    {
        self::assertSame($expected, $this->timeTraveler->removeMonth(new AbsoluteDate($from))->__toString());
    }

    /** @return iterable<string, array{string, string}> */
    public function provideRemoveMonths(): iterable
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

    /** @dataProvider provideMonthsWithReference */
    public function testAddMonthWithReference(string $reference, string $from, string $expected): void
    {
        self::assertSame($expected, $this->timeTraveler->addMonthWithReference(
            new AbsoluteDate($reference),
            new AbsoluteDate($from)
        )->__toString());
    }

    /** @return array{string, string, string}[] */
    public function provideMonthsWithReference(): iterable
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
    }

    /** @dataProvider provideMonthsWithReferenceOverYear */
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
    public function provideMonthsWithReferenceOverYear(): iterable
    {
        yield ['2020-01-01', '2021-01-01'];
        yield ['2020-01-15', '2021-01-15'];
        yield ['2020-01-28', '2021-01-28'];
        yield ['2020-01-29', '2021-01-29'];
        yield ['2020-01-30', '2021-01-30'];
        yield ['2020-01-31', '2021-01-31'];
        yield ['2020-06-30', '2021-06-30'];
    }

    /** @dataProvider provideYears */
    public function testAddYear(string $from, string $expected): void
    {
        self::assertSame($expected, $this->timeTraveler->addYear(new AbsoluteDate($from))->__toString());
    }

    /** @return array{string, string}[] */
    public function provideYears(): iterable
    {
        yield ['2020-01-01', '2021-01-01'];
        yield ['2020-01-31', '2021-01-31'];
        yield ['2020-02-29', '2021-02-28'];
        yield ['2020-06-30', '2021-06-30'];
    }
}
