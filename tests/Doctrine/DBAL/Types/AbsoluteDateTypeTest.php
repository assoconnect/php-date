<?php

declare(strict_types=1);

namespace AssoConnect\PHPDate\Tests\Doctrine\DBAL\Types;

use AssoConnect\PHPDate\AbsoluteDate;
use AssoConnect\PHPDate\Doctrine\DBAL\Types\AbsoluteDateType;
use Doctrine\DBAL\Types\ConversionException;

use function date_default_timezone_set;

class DateTest extends BaseDateTypeTestCase
{
    /**
     * {@inheritDoc}
     */
    protected function setUp(): void
    {
        $this->type = new AbsoluteDateType();

        parent::setUp();
    }

    protected function getInstanceOfPHPValue()
    {
        return new AbsoluteDate();
    }

    public function testDateConvertsToPHPValue(): void
    {
        // Birthday of jwage and also birthday of Doctrine. Send him a present ;)
        self::assertInstanceOf(
            AbsoluteDate::class,
            $this->type->convertToPHPValue('1985-09-01', $this->platform)
        );
    }

    public function testDateResetsNonDatePartsToZeroUnixTimeValues(): void
    {
        $date = $this->type->convertToPHPValue('1985-09-01', $this->platform);

        self::assertEquals('00:00:00', $date->format('H:i:s'));
    }

    public function testDateRestsSummerTimeAffection(): void
    {
        date_default_timezone_set('Europe/Berlin');

        $date = $this->type->convertToPHPValue('2009-08-01', $this->platform);
        self::assertEquals('00:00:00', $date->format('H:i:s'));
        self::assertEquals('2009-08-01', $date->format('Y-m-d'));

        $date = $this->type->convertToPHPValue('2009-11-01', $this->platform);
        self::assertEquals('00:00:00', $date->format('H:i:s'));
        self::assertEquals('2009-11-01', $date->format('Y-m-d'));
    }

    public function testInvalidDateFormatConversion(): void
    {
        $this->expectException(ConversionException::class);
        $this->type->convertToPHPValue('abcdefg', $this->platform);
    }
}
