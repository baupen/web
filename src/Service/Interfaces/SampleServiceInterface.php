<?php

namespace App\Service\Interfaces;

use App\Entity\ConstructionManager;
use App\Entity\ConstructionSite;

interface SampleServiceInterface
{
    public const string SAMPLE_SIMPLE = 'Simple';
    public const string SAMPLE_SUN_PARK = 'Sun_Park';
    public const string SAMPLE_TIERHOTEL = 'Tierhotel';
    public const string TEST = 'Test';

    public const ALL_SAMPLES = [self::SAMPLE_SIMPLE, self::SAMPLE_SUN_PARK, self::SAMPLE_TIERHOTEL];

    public function createSampleConstructionSite(string $sampleName, ConstructionManager $constructionManager): ConstructionSite;
}
