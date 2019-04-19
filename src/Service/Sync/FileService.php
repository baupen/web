<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service\Sync;

use App\Entity\Traits\FileTrait;
use App\Service\Sync\Interfaces\FileServiceInterface;
use function array_key_exists;
use const DIRECTORY_SEPARATOR;

class FileService implements FileServiceInterface
{
    /**
     * @param string $folder
     * @param string $ending
     * @param FileTrait[] $knownFiles
     * @param callable $createNewFile
     *
     * @return FileTrait[]
     */
    public function getNewFiles(string $folder, string $ending, array $knownFiles, callable $createNewFile)
    {
        $knownFilesLookup = [];
        foreach ($knownFiles as $knownFile) {
            $knownFilesLookup[$knownFile->getFilename()] = $knownFile;
        }

        /** @var FileTrait[] $newFiles */
        $newFiles = [];

        $folderLength = mb_strlen($folder);
        $files = glob($folder . DIRECTORY_SEPARATOR . '*' . $ending);
        foreach ($files as $file) {
            $fileName = mb_substr($file, $folderLength + 1);

            if (!array_key_exists($fileName, $knownFilesLookup)) {
                /** @var FileTrait $fileTrait */
                $fileTrait = $createNewFile($file);
                $fileTrait->setFilename($fileName);
                $fileTrait->setHash(hash_file('sha256', $file));
                $newFiles[] = $fileTrait;
            }
        }

        return $newFiles;
    }
}
