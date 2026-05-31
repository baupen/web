<?php

namespace App\Extension;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\DateTimeType;

/**
* @deprecated Use UTCDateTimeImmutableType instead.
*/
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

            $value = $value->setTimezone(self::getUtc());
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

        return $converted->setTimezone(self::getLocal());
    }

    private static function getUtc(): \DateTimeZone
    {
        return self::$utc ?: self::$utc = new \DateTimeZone('UTC');
    }

    private static function getLocal(): \DateTimeZone
    {
        return self::$local ?: self::$local = new \DateTimeZone('Europe/Zurich');
    }
}
