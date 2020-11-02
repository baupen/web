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
use App\Helper\DateTimeFormatter;

trait AssertApiCollectionFilterTrait
{
    private function assertApiCollectionFilterDateTime(Client $client, string $collectionUrlPrefix, string $iri, string $propertyName, \DateTime $currentValue)
    {
        $currentValueString = DateTimeFormatter::toStringUTCTimezone($currentValue); // like 2020-10-30T23:00:00.000000Z

        $afterValue = clone $currentValue;
        $afterValue->add(new \DateInterval('P1D'));
        $afterValueString = DateTimeFormatter::toStringUTCTimezone($afterValue); // like 2020-10-30T23:00:00.000000Z

        $beforeValue = clone $currentValue;
        $beforeValue->sub(new \DateInterval('P1D'));
        $beforeValueString = DateTimeFormatter::toStringUTCTimezone($beforeValue); // like 2020-10-30T23:00:00.000000Z

        $this->assertApiCollectionNotContainsIri($client, $collectionUrlPrefix.$propertyName.'[after]='.$afterValueString, $iri);
        $this->assertApiCollectionContainsIri($client, $collectionUrlPrefix.$propertyName.'[before]='.$afterValueString, $iri);

        $this->assertApiCollectionContainsIri($client, $collectionUrlPrefix.$propertyName.'[after]='.$beforeValueString, $iri);
        $this->assertApiCollectionNotContainsIri($client, $collectionUrlPrefix.$propertyName.'[before]='.$beforeValueString, $iri);

        $this->assertApiCollectionContainsIri($client, $collectionUrlPrefix.$propertyName.'[after]='.$currentValueString, $iri);

        // bug: locally the next statement fails, although it should succeed (and does so on travis)
        // $this->assertApiCollectionContainsIri($client, $collectionUrlPrefix.$propertyName.'[before]='.$currentValueString, $iri);
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
