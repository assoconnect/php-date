<?php

declare(strict_types=1);

namespace AssoConnect\PHPDate;

class AbsoluteDatePeriod
{
    private AbsoluteDate $start;

    private AbsoluteDate $end;

    public function __construct(AbsoluteDate $start, AbsoluteDate $end)
    {
        $this->start = $start;
        $this->end = $end;
    }

    public function getStart(): AbsoluteDate
    {
        return $this->start;
    }

    public function getEnd(): AbsoluteDate
    {
        return $this->end;
    }
}
