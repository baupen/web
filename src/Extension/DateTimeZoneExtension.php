<?php

declare(strict_types=1);

namespace Uzh\Zi\Extension;

use DateTimeZone;

class DateTimeZoneExtension
{
    private static ?DateTimeZone $utc = null;
    private static ?DateTimeZone $local = null;

    public static function getLocal(): DateTimeZone
    {
        if (self::$local === null) {
            self::$local = new DateTimeZone('Europe/Zurich');
        }

        return self::$local;
    }

    public static function getUtc(): DateTimeZone
    {
        if (self::$utc === null) {
            self::$utc = new DateTimeZone('UTC');
        }

        return self::$utc;
    }
}
