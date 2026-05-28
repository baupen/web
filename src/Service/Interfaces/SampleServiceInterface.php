<?php

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
