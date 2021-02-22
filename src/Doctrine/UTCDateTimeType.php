<?php


namespace App\Doctrine;


use DateTimeInterface;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\DateTimeType;

class UTCDateTimeType extends DateTimeType
{
    /**
     * @var \DateTimeZone
     */
    private static $utc;

    /**
     * @var \DateTimeZone
     */
    private static $local;

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof DateTimeInterface) {
            $originalTimezone = $value->getTimezone();

            $value->setTimezone(self::getUtc());
            $result = $value->format($platform->getDateTimeFormatString());

            // reset timezone in case datetime is reused
            $value->setTimezone($originalTimezone);

            return $result;
        }

        throw ConversionException::conversionFailedInvalidType($value, $this->getName(), ['null', 'DateTime']);
    }

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if (null === $value || $value instanceof \DateTime) {
            return $value;
        }

        $converted = \DateTime::createFromFormat(
            $platform->getDateTimeFormatString(),
            $value,
            self::getUtc()
        );

        if (!$converted) {
            throw ConversionException::conversionFailedFormat(
                $value,
                $this->getName(),
                $platform->getDateTimeFormatString()
            );
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
     * Includes UTC handling
     */
    public static function tryParseDateTime(string $value): \DateTime
    {
        // this assumes the datetime stored by the DB can be parsed by \DateTime (which is reasonable)
        // a better solution would need to replicated the behaviour of convertToPHPValue as seen above
        $dateTime = new \DateTime($value, self::getUtc());
        $dateTime->setTimezone(self::getLocal());

        return $dateTime;
    }
}
