<?php

declare(strict_types=1);

namespace AssoConnect\PHPDate;

use AssoConnect\PHPDate\Exception\ParsingException;

class AbsoluteDate
{
    public const DEFAULT_DATE_FORMAT = 'Y-m-d';

    /**
     * @var \DateTimeInterface
     */
    private $datetime;

    /**
     * AbsoluteDate constructor from a date as string
     * Use createInTimezone method if you have a DateTime object or your format includes the hour part
     *
     * @param string $date Date as string
     * @param string $format Format to parse the provided date
     */
    public function __construct(string $date, string $format = null)
    {
        if (!$format) {
            $format = self::DEFAULT_DATE_FORMAT;
        }

        $timezone = new \DateTimeZone('UTC');
        $format .= 'H:i:s';
        $date .= '00:00:00';

        $this->datetime = \DateTime::createFromFormat($format, $date, $timezone);

        if (false === $this->datetime) {
            throw new ParsingException(sprintf('Cannot parse %s with format %s', $date, $format));
        }
    }

    /**
     * Returns date formatted according to given format
     *
     * Accepts any format supported by DateTime::format()
     * @link https://www.php.net/manual/en/datetime.format.php
     */
    public function format(string $format = self::DEFAULT_DATE_FORMAT): string
    {
        return $this->datetime->format($format);
    }

    /**
     * Modify the internal datetime
     * Supported modify strings :
     * @link https://www.php.net/manual/fr/datetime.formats.php
     * @return AbsoluteDate
     */
    public function modify(string $modify): self
    {
        $this->datetime = $this->datetime->modify($modify);
        return $this;
    }

    /**
     * Return the DateTime for a given DateTimeZone
     * @param \DateTimeZone $timezone
     * @return \DateTime
     */
    public function startsAt(\DateTimeZone $timezone): \DateTime
    {
        return $this->getDateTimeFromFormatAndTimezone(self::DEFAULT_DATE_FORMAT, $timezone);
    }

    /**
     * Return the DateTime at the end of the day for a given DateTimeZone
     * @param \DateTimeZone $timezone
     * @return \DateTime
     */
    public function endsAt(\DateTimeZone $timezone): \DateTime
    {
        return $this->getDateTimeFromFormatAndTimezone(self::DEFAULT_DATE_FORMAT . ' 23:59:59', $timezone);
    }

    private function getDateTimeFromFormatAndTimezone(string $format, \DateTimeZone $timezone): \DateTime
    {
        return new \DateTime($this->format($format), $timezone);
    }

    /**
     * Returns date formatted according to the default date format (Y-m-d)
     *
     * @return string
     */
    public function __toString()
    {
        return $this->format(self::DEFAULT_DATE_FORMAT);
    }

    /**
     * Returns an AbsoluteDate object
     *
     * @param \DateTimeZone $timezone Timezone to use to get the right date
     * @param \DateTimeInterface|null $datetime Point in time to find the date from
     * @return static
     * @throws \Exception
     */
    public static function createInTimezone(\DateTimeZone $timezone, \DateTimeInterface $datetime = null): self
    {
        $datetime = new \DateTime('@' . ($datetime ? $datetime->getTimestamp() : time()));
        $datetime->setTimezone($timezone);

        return new self($datetime->format(self::DEFAULT_DATE_FORMAT));
    }
}
