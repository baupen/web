<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service\Interfaces;

use App\Entity\ConstructionManager;
use App\Entity\ConstructionSite;

interface SampleServiceInterface
{
    const SAMPLE_SIMPLE = 'Simple';
    const SAMPLE_SUN_PARK = 'Sun_Park';
    const SAMPLE_TIERHOTEL = 'Tierhotel';
    const TEST = 'Test';

    const ALL_SAMPLES = [self::SAMPLE_SIMPLE, self::SAMPLE_SUN_PARK, self::SAMPLE_TIERHOTEL];

    public function createSampleConstructionSite(string $sampleName, ConstructionManager $constructionManager): ConstructionSite;
}
