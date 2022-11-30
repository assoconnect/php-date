<?php

declare(strict_types=1);

namespace AssoConnect\PHPDate;

/**
 * This class addresses corner-case of the modify('+1 month') method
 */
class TimeTraveler
{
    /**
     * Adds at most one month to a given date
     *
     * (new AbsoluteDate(2020-01-31))->modify('+1 month') = 2020-02-31 => 2020-03-02 so more than a month
     * addMonth(new AbsoluteDate(2020-01-31)) = 2020-02-29
     */
    public function addMonth(AbsoluteDate $from): AbsoluteDate
    {
        $expectedMonth = (intval($from->format('n')) % 12) + 1;
        $next = $from->modify('+1 month');
        while ($expectedMonth !== intval($next->format('n'))) {
            $next = $next->modify('-1 day');
        }
        return $next;
    }

    /**
     * Ensures that months calculation are coherent year over year
     *
     * (new AbsoluteDate(2020-01-31))->modify('+1 year') = 2021-01-31
     * (new AbsoluteDate(2020-01-31))
     *   ->modify('+1 month') = 2020-02-31 => 2020-03-02
     *   ->modify('+1 month') = 2020-04-02
     *   ...
     *   ->modify('+1 month') = 2021-02-02 but we would expect 2021-01-31
     *
     * $this->addMonth(
     *   $this->addMonth(
     *     ...
     *       $this->addMonth(new AbsoluteDate(2020-01-31))
     *     ...
     *   )
     * ) = 2021-01-28 but we would expect 2021-01-31
     */
    public function addMonthWithReference(AbsoluteDate $reference, AbsoluteDate $from): AbsoluteDate
    {
        $next = $this->addMonth($from);
        $day = min(
            intval($reference->format('j')),
            intval($next->modify('last day of this month')->format('j'))
        );
        $dayString = str_pad(strval($day), 2, '0', STR_PAD_LEFT);
        return new AbsoluteDate($next->format('Y-m') . '-' . $dayString);
    }

    /**
     * Add at most a year to a date
     *
     * (new AbsoluteDate(2020-02-29))->modify('+1 year') = 2021-02-29 => 2020-03-01 so more than a year
     */
    public function addYear(AbsoluteDate $from): AbsoluteDate
    {
        $expectedMonth = intval($from->format('n'));
        $next = $from->modify('+1 year');
        while ($expectedMonth !== intval($next->format('n'))) {
            $next = $next->modify('-1 day');
        }
        return $next;
    }
}
