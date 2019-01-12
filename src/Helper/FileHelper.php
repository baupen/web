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
    /**
     * @param $sourceFolder
     * @param $destinationFolder
     *
     * @throws \Exception
     */
    public static function copyRecursively($sourceFolder, $destinationFolder)
    {
        if (!is_dir($destinationFolder)) {
            mkdir($destinationFolder);
        }

        $dir = opendir($sourceFolder);
        if ($dir === false) {
            throw new \Exception('failed to open dir ' . $dir);
        }

        while (false !== ($file = readdir($dir))) {
            if (($file !== '.') && ($file !== '..')) {
                if (is_dir($sourceFolder . '/' . $file)) {
                    self::copyRecursively($sourceFolder . '/' . $file, $destinationFolder . '/' . $file);
                } else {
                    copy($sourceFolder . '/' . $file, $destinationFolder . '/' . $file);
                }
            }
        }

        closedir($dir);
    }
}
