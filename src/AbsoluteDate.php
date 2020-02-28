<?php

declare(strict_types=1);

namespace AssoConnect\PHPDate;

use AssoConnect\PHPDate\Exception\ParsingException;

class AbsoluteDate
{
    public const DEFAULT_DATE_FORMAT = 'Y-m-d';

    /**
     * @var \DateTimeImmutable
     */
    private $dateTime;

    /**
     * AbsoluteDate constructor.
     * @param string $date Date as string
     *                     If none is provided then the current date in the UTC timezone is used
     * @param string $format Format to parse the provided date
     *                     If none is given then the Y-m-d format is used
     */
    public function __construct(string $date = 'now', string $format = null)
    {
        $timezone = new \DateTimeZone('UTC');

        if (null === $format) {
            $datetime = new \DateTimeImmutable('now' === $date ? sprintf('@%d', time()) : $date);
        } else {
            $datetime = \DateTimeImmutable::createFromFormat($format, $date, $timezone);
        }

        if (false === $datetime) {
            throw new ParsingException(sprintf('Cannot parse %s with format %s', $date, $format));
        }

        $this->dateTime = $datetime->setTime(0, 0);
    }

    /**
     * Returns date formatted according to given format
     *
     * Accepts any format supported by DateTime::format()
     * @link https://www.php.net/manual/en/datetime.format.php
     */
    public function format(string $format = self::DEFAULT_DATE_FORMAT): string
    {
        return $this->dateTime->format($format);
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

    public static function fromTimestamp(int $timestamp): AbsoluteDate
    {
        return new self(sprintf('@%d', $timestamp));
    }

    public function toTimestamp(): int
    {
        return $this->dateTime->getTimestamp();
    }


    public static function fromDateTime(\DateTimeInterface $dateTime): AbsoluteDate
    {
        return new self($dateTime->format(self::DEFAULT_DATE_FORMAT));
    }

    public function toDateTime(): \DateTime
    {
        $dateTime = new \DateTime();

        return $dateTime->setTimestamp($this->dateTime->getTimestamp());
    }


    public function toDateTimeImmutable(): \DateTimeImmutable
    {
        return $this->dateTime;
    }

    /**
     * Returns a new instance of absolute date
     */
    public function modify(string $modify): AbsoluteDate
    {
        $newDateTime = $this->dateTime->modify($modify);

        return self::fromDateTime($newDateTime);
    }
}
