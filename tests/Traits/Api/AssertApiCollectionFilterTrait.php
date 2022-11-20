<?php

/*
 * This file is part of the baupen project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests\Traits\Api;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\Client;

trait AssertApiCollectionFilterTrait
{
    private function assertApiCollectionFilterDateTime(Client $client, string $collectionUrlPrefix, string $iri, string $propertyName, \DateTime $currentValue)
    {
        $format = function (\DateTime $dateTime) {
            return urlencode($dateTime->format('c'));
        };

        $formatAlternative = function (\DateTime $dateTime) {
            $utcDateTime = clone $dateTime;
            $utcDateTime->setTimezone(new \DateTimeZone('UTC'));

            return urlencode($utcDateTime->format('Y-m-d\TH:i:s.u\Z'));
        };

        // after and before are both inclusive
        $this->assertApiCollectionContainsIri($client, $collectionUrlPrefix.$propertyName.'[after]='.$format($currentValue), $iri);
        $this->assertApiCollectionContainsIri($client, $collectionUrlPrefix.$propertyName.'[before]='.$format($currentValue), $iri);

        $afterValue = clone $currentValue;
        $afterValue->add(new \DateInterval('PT1M'));
        $this->assertApiCollectionNotContainsIri($client, $collectionUrlPrefix.$propertyName.'[after]='.$format($afterValue), $iri);
        $this->assertApiCollectionContainsIri($client, $collectionUrlPrefix.$propertyName.'[before]='.$format($afterValue), $iri);

        $beforeValue = clone $currentValue;
        $beforeValue->sub(new \DateInterval('PT1M'));
        $this->assertApiCollectionContainsIri($client, $collectionUrlPrefix.$propertyName.'[after]='.$format($beforeValue), $iri);
        $this->assertApiCollectionNotContainsIri($client, $collectionUrlPrefix.$propertyName.'[before]='.$format($beforeValue), $iri);

        // ensure timezones & different formatting respected
        $this->assertApiCollectionContainsIri($client, $collectionUrlPrefix.$propertyName.'[after]='.$formatAlternative($currentValue).'&'.$propertyName.'[before]='.$format($currentValue), $iri);
    }

    private function assertApiCollectionFilterBoolean(Client $client, string $collectionUrlPrefix, string $iri, string $propertyName, bool $currentValue)
    {
        $trueValues = ['true', '1'];
        $falseValues = ['false', '0'];

        $currentValueToString = $currentValue ? $trueValues : $falseValues;
        $notCurrentValueToString = !$currentValue ? $trueValues : $falseValues;

        foreach ($currentValueToString as $stringValue) {
            $this->assertApiCollectionContainsIri($client, $collectionUrlPrefix.$propertyName.'='.$stringValue, $iri);
        }
        foreach ($notCurrentValueToString as $stringValue) {
            $this->assertApiCollectionNotContainsIri($client, $collectionUrlPrefix.$propertyName.'='.$stringValue, $iri);
        }
    }

    private function assertApiCollectionFilterSearchPartial(Client $client, string $collectionUrlPrefix, string $iri, string $propertyName, string $currentValue)
    {
        $startPart = substr($currentValue, 0, -1);
        $middlePart = substr($currentValue, 1, -2);
        $endPart = substr($currentValue, 1);

        $foundParts = [$startPart, $middlePart, $endPart];

        foreach ($foundParts as $foundPart) {
            $this->assertApiCollectionContainsIri($client, $collectionUrlPrefix.$propertyName.'='.$foundPart, $iri);
        }

        $this->assertApiCollectionNotContainsIri($client, $collectionUrlPrefix.$propertyName.'='.$currentValue.'null', $iri);
    }

    private function assertApiCollectionFilterSearchExact(Client $client, string $collectionUrlPrefix, string $iri, string $propertyName, string $currentValue)
    {
        $invalidPart = substr($currentValue, 0, -1);

        // check single
        $this->assertApiCollectionNotContainsIri($client, $collectionUrlPrefix.$propertyName.'='.$invalidPart, $iri);
        $this->assertApiCollectionContainsIri($client, $collectionUrlPrefix.$propertyName.'='.$currentValue, $iri);

        // check multiple syntax, with single value
        $this->assertApiCollectionContainsIri($client, $collectionUrlPrefix.$propertyName.'[]='.$currentValue, $iri);
        $this->assertApiCollectionNotContainsIri($client, $collectionUrlPrefix.$propertyName.'[]='.$invalidPart, $iri);

        // check multiple syntax, with multiple values
        $this->assertApiCollectionContainsIri($client, $collectionUrlPrefix.$propertyName.'[]='.$currentValue.'&'.$propertyName.'[]='.$invalidPart, $iri);
        $this->assertApiCollectionNotContainsIri($client, $collectionUrlPrefix.$propertyName.'[]='.$invalidPart.'&'.$propertyName.'[]='.$invalidPart, $iri);
    }
}
