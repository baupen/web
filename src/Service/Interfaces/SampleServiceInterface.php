<?php

/*
 * This file is part of the baupen project.
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
    public const SAMPLE_SIMPLE = 'Simple';
    public const SAMPLE_SUN_PARK = 'Sun_Park';
    public const SAMPLE_TIERHOTEL = 'Tierhotel';
    public const TEST = 'Test';

    public const ALL_SAMPLES = [self::SAMPLE_SIMPLE, self::SAMPLE_SUN_PARK, self::SAMPLE_TIERHOTEL];

    public function createSampleConstructionSite(string $sampleName, ConstructionManager $constructionManager): ConstructionSite;
}
