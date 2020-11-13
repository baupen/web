<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\DataFixtures\Model;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class AssetFile extends UploadedFile
{
    public function __construct(string $path)
    {
        $originalName = pathinfo($path, PATHINFO_BASENAME);

        $newPath = $path.'.tmp';
        copy($path, $newPath);

        parent::__construct($newPath, $originalName, null, null, true);
    }
}
