<?php

/*
 * This file is part of the baupen project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service\Upload;

class UploadFileCheck
{
    private bool $uploadPossible;

    /**
     * @var string[]
     */
    private ?array $sameHashConflicts = null;

    private ?string $fileNameConflict = null;

    private ?string $derivedFileName = null;

    /**
     * @return string[]
     */
    public function getSameHashConflicts(): array
    {
        return $this->sameHashConflicts;
    }

    /**
     * @param string[] $sameHashConflicts
     */
    public function setSameHashConflicts(array $sameHashConflicts): void
    {
        $this->sameHashConflicts = $sameHashConflicts;
    }

    public function getFileNameConflict(): ?string
    {
        return $this->fileNameConflict;
    }

    public function setFileNameConflict(?string $fileNameConflict): void
    {
        $this->fileNameConflict = $fileNameConflict;
    }

    public function getDerivedFileName(): ?string
    {
        return $this->derivedFileName;
    }

    public function setDerivedFileName(?string $derivedFileName): void
    {
        $this->derivedFileName = $derivedFileName;
    }

    public function isUploadPossible(): bool
    {
        return $this->uploadPossible;
    }

    public function setUploadPossible(bool $uploadPossible): void
    {
        $this->uploadPossible = $uploadPossible;
    }
}
