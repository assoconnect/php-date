<?php

declare(strict_types=1);

namespace AssoConnect\PHPDate;

use AssoConnect\PHPDate\Exception\UnknownPatternException;
use AssoConnect\PHPDate\Exception\UnsupportedLocalException;
use IntlDateFormatter;

class LocalizedStringParser
{
    /**
     * Returns an AbsoluteDate instance from a formatted string like 9/1/23 for September, 1st 2023 in the USA
     * @param string $date Formatted date
     * @param string $locale Locale used to format the date (both en-US & en_US patterns are supported)
     */
    public function create(string $date, string $locale): AbsoluteDate
    {
        // ⚠️ IntlDateFormatter & DateTimeInterface don't use the same patterns
        $parts = \Safe\array_combine(
            explode('/', $this->getPatternFromLocale($locale)), // IntlDateFormatter pattern
            explode('/', $date)
        );
        $orderedDateParts = ['', '', ''];
        $patternParts = ['', '', '']; // DateTimeInterface pattern
        foreach ($parts as $pattern => $value) {
            switch ($pattern) {
                case 'd': // Day without leading 0
                case 'dd': // Day with leading 0
                    $orderedDateParts[2] = str_pad($value, 2, '0', STR_PAD_LEFT);
                    $patternParts[2] = 'd';
                    break;
                case 'M': // Month without leading 0
                case 'MM': // Month with leading 0
                    $orderedDateParts[1] = str_pad($value, 2, '0', STR_PAD_LEFT);
                    $patternParts[1] = 'm';
                    break;
                case 'y': // Year on 4 digits
                case 'yy': // Year on 2 digits
                case 'yyyy': // Year on 4 digits
                    $orderedDateParts[0] = $value;
                    $patternParts[0] = (2 === strlen($value)) ? 'y' : 'Y';
                    break;
                default:
                    throw new UnknownPatternException($pattern);
            }
        }
        return new AbsoluteDate(
            implode('-', $orderedDateParts),
            implode('-', $patternParts)
        );
    }

    public function getPatternFromLocale(string $locale): string
    {
        $formatter = new IntlDateFormatter(
            $locale,
            IntlDateFormatter::SHORT,
            IntlDateFormatter::NONE,
        );

        $pattern = $formatter->getPattern();

        if (false === $pattern) {
            throw new UnsupportedLocalException($locale);
        }

        return $pattern;
    }
}
