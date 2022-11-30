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
        yield ['2020-02-29', '2020-03-29'];
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
    }

    /** @dataProvider provideMonthsWithReferenceOverYear */
    public function testAddMonthWithReferenceWorksYearOverYear(string $reference, string $expected): void
    {
        $reference = new AbsoluteDate($reference);

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
    }
}