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
        $this->assertApiCollectionNotContainsIri($client, $collectionUrlPrefix.$propertyName.'[before]='.$currentValueString, $iri);
    }
}
