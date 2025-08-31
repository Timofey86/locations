<?php

namespace App\Helper;

use DateTimeImmutable;

class DateTimeHelper
{
    public static function isValidDate(string $date): bool
    {
        $dateTime = DateTimeImmutable::createFromFormat('Y-m-d', $date);
        return $dateTime && $dateTime->format('Y-m-d') === $date;
    }
}
