<?php

namespace App\Service\Interfaces;

use App\Entity\MapFile;

interface MapFileServiceInterface
{
    public function renderForMobileDevice(MapFile $mapFile): ?string;
}
