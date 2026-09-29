<?php

declare(strict_types=1);

namespace AssoConnect\PHPDate;

/**
 * This class addresses corner-case of the ->modify('+/- 1 month') method
 */
class TimeTraveler
{
    /**
     * Adds at most one month to a given date
     *
     * (new AbsoluteDate(2020-01-31))->modify('+1 month') = 2020-02-31 => 2020-03-02 so more than a month
     * addMonth(new AbsoluteDate(2020-01-31)) = 2020-02-29
     *
     * (new AbsoluteDate(2020-06-30))->modify('+1 month') = 2020-07-30 so less than a month
     * addMonth(new AbsoluteDate(2020-06-30)) = 2020-07-31
     */
    public function addMonth(AbsoluteDate $from): AbsoluteDate
    {
        $currentMonth = intval($from->format('n'));
        $expectedMonth = $currentMonth + 1 === 13 ? 1 : $currentMonth + 1;

        $next = $from->modify('+1 month');

        while ($expectedMonth !== intval($next->format('n'))) {
            $next = $next->modify('-1 day');
        }

        return $this->modifyForTheLastDayOfThisMonthIfNeedBe($next, $from);
    }

    public function removeMonth(AbsoluteDate $from): AbsoluteDate
    {
        $currentMonth = intval($from->format('n'));
        $expectedMonth = $currentMonth - 1 === 0 ? 12 : $currentMonth - 1;

        $previous = $from->modify('-1 month');

        while ($expectedMonth !== intval($previous->format('n'))) {
            $previous = $previous->modify('-1 day');
        }

        return $this->modifyForTheLastDayOfThisMonthIfNeedBe($previous, $from);
    }

    /**
     * The last occurrence of the reference day of month that is at most one month after $from
     *
     * The result never goes past addMonth($from). When the reference day of that month is already past it, the
     * result steps back to the reference day of the month of $from, so a date that drifted away from the
     * reference day rejoins it without ever spanning more than a month:
     * addMonthWithReference(2020-01-31, 2020-08-01) = 2020-08-31, not 2020-09-30
     * addMonthWithReference(2020-01-31, 2020-08-16) = 2020-08-31
     * addMonthWithReference(2020-01-15, 2020-08-20) = 2020-09-15
     * The result may be the very next day when $from is the day before the reference day:
     * addMonthWithReference(2020-01-31, 2020-07-30) = 2020-07-31
     *
     * A $from on the reference day gets the reference day of the following month, which keeps months calculation
     * coherent year over year where addMonth() alone drifts after a shorter month:
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
        $oneMonthLater = $this->addMonth($from);

        $inTheMonthAfter = $this->referenceDayWithinMonthOf($oneMonthLater, $reference);
        if ($inTheMonthAfter->isBeforeOrEqualTo($oneMonthLater)) {
            return $inTheMonthAfter;
        }

        return $this->referenceDayWithinMonthOf($from, $reference);
    }

    /**
     * The reference day placed in the month of $date, clamped to that month's length — or that month's last day
     * when the reference is itself the last day of its own month
     */
    private function referenceDayWithinMonthOf(AbsoluteDate $date, AbsoluteDate $reference): AbsoluteDate
    {
        $day = min(
            intval($reference->format('j')),
            intval($date->modify('last day of this month')->format('j'))
        );
        $dayString = str_pad(strval($day), 2, '0', STR_PAD_LEFT);
        return $this->modifyForTheLastDayOfThisMonthIfNeedBe(
            new AbsoluteDate($date->format('Y-m') . '-' . $dayString),
            $reference
        );
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

    private function modifyForTheLastDayOfThisMonthIfNeedBe(AbsoluteDate $result, AbsoluteDate $reference): AbsoluteDate
    {
        $pattern = 'last day of this month';
        if ($reference->equalsTo($reference->modify($pattern))) {
            return $result->modify($pattern);
        }
        return $result;
    }
}
