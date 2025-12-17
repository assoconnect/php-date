<?php

declare(strict_types=1);

namespace AssoConnect\PHPDate;

use AssoConnect\PHPDate\Exception\ParsingException;
use DateTimeZone;
use Symfony\Component\Clock\Clock;
use Symfony\Component\Clock\DatePoint;

class AbsoluteDate implements \Stringable
{
    public const DEFAULT_DATE_FORMAT = 'Y-m-d';

    private DatePoint $datetime;

    /**
     * AbsoluteDate constructor from a date as string
     * Use createInTimezone method if you have a DateTime instance or your format includes the hour part
     *
     * @param string $date Date as string
     * @param string $format Format to parse the provided date
     */
    public function __construct(string $date, string $format = self::DEFAULT_DATE_FORMAT)
    {
        $this->initDatetime($date, $format);
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
     *
     * This method only supports year, month, and day modifiers
     * @link https://www.php.net/manual/fr/datetime.formats.php
     *
     * @see TimeTraveler for coherent modifications month-over-month & year-over-year
     *
     * @return AbsoluteDate
     */
    public function modify(string $modifier): self
    {
        $validPatterns = [
            'day',
            'days',
            'month',
            'months',
            'year',
            'years',
            'week',
            'weeks',
            'last',
            'first',
            'ago',
            'this',
            'of',
            'previous',
        ];
        preg_match_all('/([a-z]+)/', $modifier, $matches);
        $invalidPatterns = array_diff($matches[0], $validPatterns);
        if ([] !== $invalidPatterns) {
            throw new \DomainException(sprintf(
                'Only modification on day, week, month, and year are allowed. Invalid patterns are: %s',
                implode(', ', $invalidPatterns)
            ));
        }

        return new self($this->datetime->modify($modifier)->format(self::DEFAULT_DATE_FORMAT));
    }

    /**
     * Return the DateTime for a given DateTimeZone
     */
    public function startsAt(DateTimeZone $timezone): DatePoint
    {
        return $this->getDateTimeFromFormatAndTimezone(self::DEFAULT_DATE_FORMAT . ' 00:00:00', $timezone);
    }

    /**
     * Return the DateTime at the end of the day for a given DateTimeZone
     */
    public function endsAt(DateTimeZone $timezone): DatePoint
    {
        return $this->getDateTimeFromFormatAndTimezone(self::DEFAULT_DATE_FORMAT . ' 23:59:59', $timezone);
    }

    private function getDateTimeFromFormatAndTimezone(string $format, DateTimeZone $timezone): DatePoint
    {
        return new DatePoint($this->format($format), $timezone);
    }

    /**
     * Checks whether the date represented by this instance equals to the other.
     */
    public function equalsTo(self $other): bool
    {
        return $this->__toString() === $other->__toString();
    }

    /**
     * @deprecated use equalsTo
     */
    public function equals(self $other): bool
    {
        return $this->equalsTo($other);
    }

    /**
     * Compare this date with another date
     * @return int [-1,0,1] If this date is before, on, or after the given date.
     */
    public function compare(self $other): int
    {
        return $this->__toString() <=> $other->__toString();
    }

    /**
     * Returns whether this date is before another.
     */
    public function isBefore(self $other): bool
    {
        return $this->__toString() < $other->__toString();
    }

    /**
     * Returns whether this date is before or equal to another.
     */
    public function isBeforeOrEqualTo(self $other): bool
    {
        return $this->__toString() <= $other->__toString();
    }

    /**
     * Returns whether this date is after another.
     */
    public function isAfter(self $other): bool
    {
        return $this->__toString() > $other->__toString();
    }

    /**
     * Returns whether this date is after or equal to another.
     */
    public function isAfterOrEqualTo(self $other): bool
    {
        return $this->__toString() >= $other->__toString();
    }

    public function isBetween(self $other1, self $other2): bool
    {
        return $other1->__toString() < $this->__toString() && $this->__toString() < $other2->__toString();
    }

    public function isBetweenOrEqualTo(self $other1, self $other2): bool
    {
        return $other1->__toString() <= $this->__toString() && $this->__toString() <= $other2->__toString();
    }

    /**
     * Returns date formatted according to the default date format (Y-m-d)
     */
    public function __toString(): string
    {
        return $this->format();
    }

    /**
     * Returns an AbsoluteDate instance
     *
     * @param DateTimeZone $timezone Timezone to use to get the right date
     * @param \DateTimeInterface|null $datetime Point in time to find the date from
     * @throws \Exception
     */
    public static function createInTimezone(DateTimeZone $timezone, \DateTimeInterface $datetime = null): self
    {
        $pointInTime = new DatePoint(
            '@' . (null === $datetime ? Clock::get()->now()->getTimestamp() : $datetime->getTimestamp())
        );

        $pointInTime = $pointInTime->setTimezone($timezone);

        $localMidnight = DatePoint::createFromFormat(
            'Y-m-d H:i:s',
            $pointInTime->format('Y-m-d 00:00:00'),
            $timezone
        );

        $absolute = new self($localMidnight->format(self::DEFAULT_DATE_FORMAT));
        $absolute->datetime = $localMidnight;

        return $absolute;
    }

    /**
     * Returns an AbsoluteDate instance from a relative format in a given timezone
     *
     * @param string $relative Relative format to use
     * @param ?DateTimeZone $timezone Timezone to use to get the right date
     */
    public static function createRelative(string $relative = 'now', DateTimeZone $timezone = null): self
    {
        if (null === $timezone) {
            $timezone = new DateTimeZone('UTC');
        }

        $datetime = new DatePoint($relative, $timezone);

        return self::createInTimezone($timezone, $datetime);
    }

    private function initDatetime(string $date, string $format): void
    {
        $timezone = new DateTimeZone('UTC');
        $format .= 'H:i:s';
        $date .= '00:00:00';

        try {
            $this->datetime = DatePoint::createFromFormat($format, $date, $timezone);
        } catch (\DateMalformedStringException $e) {
            throw new ParsingException(sprintf('Cannot parse %s with format %s', $date, $format), previous: $e);
        }
    }

    /**
     * @return string[]
     */
    public function __serialize(): array
    {
        return [
            'date' => $this->format(),
        ];
    }

    /**
     * @param string[] $data
     */
    public function __unserialize(array $data): void
    {
        if (array_key_exists('date', $data)) {
            if (1 === preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/', $data['date'])) {
                $this->initDatetime($data['date'], self::DEFAULT_DATE_FORMAT);
                return;
            }
            // Temporary for historic data
            $this->initDatetime(unserialize($data['date']), self::DEFAULT_DATE_FORMAT);
            return;
        }

        // Temporary for historic data
        // The key looks like " AssoConnect\PHPDate\AbsoluteDate datetime" where both spaces are like \0
        // so using array_values makes the value much easier to access
        // @phpstan-ignore-next-line
        $this->initDatetime(array_values($data)[0]->format(self::DEFAULT_DATE_FORMAT), self::DEFAULT_DATE_FORMAT);
    }
}
