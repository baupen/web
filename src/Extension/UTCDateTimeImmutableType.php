<?php

declare(strict_types=1);

namespace App\Extension;

use DateTimeImmutable;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\DateTimeImmutableType;

class UTCDateTimeImmutableType extends DateTimeImmutableType
{
    /**
     * @throws ConversionException
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if ($value instanceof DateTimeImmutable) {
            $value = $value->setTimezone(DateTimeZoneExtension::getUtc());
        }

        return parent::convertToDatabaseValue($value, $platform);
    }

    /**
     * @throws ConversionException
     */
    public function convertToPHPValue($value, AbstractPlatform $platform): ?DateTimeImmutable
    {
        if ($value === null || $value instanceof DateTimeImmutable) {
            return $value;
        }

        $converted = DateTimeImmutable::createFromFormat(
            $platform->getDateTimeFormatString(),
            $value,
            DateTimeZoneExtension::getUtc(),
        );

        if (!$converted) {
            // phpcs:ignore
            throw ConversionException::conversionFailedFormat($value, 'UTCDateTimeImmutable', $platform->getDateTimeFormatString());
        }

        return $converted->setTimezone(DateTimeZoneExtension::getLocal());
    }
}
