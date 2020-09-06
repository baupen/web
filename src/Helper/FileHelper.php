<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Helper;

class FileHelper
{
    public static function ensureFolderExists(string $folderName)
    {
        if (!is_dir($folderName)) {
            mkdir($folderName, 0777, true);
        }
    }

    public static function sanitizeFileName(string $fileName)
    {
        $noUmlautFileName = str_replace(['ä', 'ö', 'ü', 'Ä', 'Ö', 'Ü'], ['ae', 'oe', 'ue', 'Ae', 'Oe', 'Ue'], $fileName);

        return preg_replace('/[^A-Za-z0-9]+/', '_', $noUmlautFileName);
    }
}
