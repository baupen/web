<?php

namespace App\Tests\DataFixtures;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class AssetFile extends UploadedFile
{
    public function __construct(string $path)
    {
        /** @var string $originalName */
        $originalName = pathinfo($path, PATHINFO_BASENAME);

        $newPath = $path . '.tmp';
        copy($path, $newPath);

        parent::__construct($newPath, $originalName, null, null, true);
    }
}
