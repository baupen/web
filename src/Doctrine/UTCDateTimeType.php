<?php

/*
 * This file is part of the baupen project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Doctrine;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\DateTimeType;

class UTCDateTimeType extends DateTimeType
{
    private static ?\DateTimeZone $utc = null;

    private static ?\DateTimeZone $local = null;

    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if (null === $value) {
            return null;
        }

        if ($value instanceof \DateTimeInterface) {
            $originalTimezone = $value->getTimezone();

            $value->setTimezone(self::getUtc());
            $result = $value->format($platform->getDateTimeFormatString());

            // reset timezone in case datetime is reused
            $value->setTimezone($originalTimezone);

            return $result;
        }

        throw ConversionException::conversionFailedInvalidType($value, $this->getName(), ['null', 'DateTime']);
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?\DateTime
    {
        if (null === $value || $value instanceof \DateTime) {
            return $value;
        }

        /** @var \DateTime|false $converted */
        $converted = \DateTime::createFromFormat(
            $platform->getDateTimeFormatString(),
            $value,
            self::getUtc()
        );

        if (!$converted) {
            throw ConversionException::conversionFailedFormat($value, $this->getName(), $platform->getDateTimeFormatString());
        }

        $converted->setTimezone(self::getLocal());

        return $converted;
    }

    private static function getUtc(): \DateTimeZone
    {
        return self::$utc ?: self::$utc = new \DateTimeZone('UTC');
    }

    private static function getLocal(): \DateTimeZone
    {
        return self::$local ?: self::$local = new \DateTimeZone(date_default_timezone_get());
    }

    /**
     * This tries to parse "best effort" the datetime returned by the database to a \DateTime object
     * Includes UTC handling.
     */
    public static function tryParseDateTime(?string $value): ?\DateTime
    {
        if (null === $value) {
            return null;
        }

        // this assumes the datetime stored by the DB can be parsed by \DateTime (which is reasonable)
        // a better solution would need to replicated the behaviour of convertToPHPValue as seen above
        $dateTime = new \DateTime($value, self::getUtc());
        $dateTime->setTimezone(self::getLocal());

        return $dateTime;
    }
}
