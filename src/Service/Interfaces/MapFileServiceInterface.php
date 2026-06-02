<?php

namespace App\Service\Interfaces;

use App\Entity\MapFile;

interface MapFileServiceInterface
{
    /**
     * optimizes the PDF for mobile devices, which had a less performant renderer
     * introduced around 2020, and was very effective notably on iOS
     * unclear whether this still brings a benefit (2026), but cheap to leave in
     */
    public function renderForMobileDevice(MapFile $mapFile): ?string;
}
