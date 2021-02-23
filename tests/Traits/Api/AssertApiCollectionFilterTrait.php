<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests\Traits\Api;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\Client;
use DateTimeZone;

trait AssertApiCollectionFilterTrait
{
    private function assertApiCollectionFilterDateTime(Client $client, string $collectionUrlPrefix, string $iri, string $propertyName, \DateTime $currentValue)
    {
        // after and before are both inclusive
        $this->assertApiCollectionContainsIri($client, $collectionUrlPrefix.$propertyName.'[after]='.$currentValue->format('c').'&'.$propertyName.'[before]='.$currentValue->format('c'), $iri);

        $afterValue = clone $currentValue;
        $afterValue->add(new \DateInterval('PT1M'));
        $this->assertApiCollectionNotContainsIri($client, $collectionUrlPrefix.$propertyName.'[after]='.$afterValue->format('c'), $iri);
        $this->assertApiCollectionContainsIri($client, $collectionUrlPrefix.$propertyName.'[before]='.$afterValue->format('c'), $iri);

        $beforeValue = clone $currentValue;
        $beforeValue->sub(new \DateInterval('PT1M'));
        $this->assertApiCollectionContainsIri($client, $collectionUrlPrefix.$propertyName.'[after]='.$beforeValue->format('c'), $iri);
        $this->assertApiCollectionNotContainsIri($client, $collectionUrlPrefix.$propertyName.'[before]='.$beforeValue->format('c'), $iri);

        // ensure timezone parsed valid
        $utcCurrentValue = clone $currentValue;
        $utcCurrentValue->setTimezone(new DateTimeZone('UTC'));
        $utcCurrentValueString = $utcCurrentValue->format('Y-m-d\TH:i:s.u\Z'); // like 2020-10-30T23:00:00.000000Z
        $this->assertApiCollectionContainsIri($client, $collectionUrlPrefix.$propertyName.'[after]='.$currentValue->format('c').'&'.$propertyName.'[before]='.$utcCurrentValueString, $iri);
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
